"""
DiseaseModelService
===================
Loads vgg16Mymodel.h5 (Keras/TensorFlow VGG16 fine-tuned on the
PlantVillage 38-class dataset) and runs image inference.

Pipeline (two-stage):
  Stage 1 — MobileNetV2 plant pre-check (ImageNet, torchvision)
              ↓ is_plant=False → return status="invalid"  (HARD STOP)
              ↓ is_plant=True
  Stage 2 — VGG16 disease classification
              ↓ → return status="success" with disease + confidence

Why two stages instead of a confidence threshold on VGG16:
  VGG16 is trained ONLY on 38 plant-leaf classes. It has no concept of
  "chair" or "person" — it will always pick the closest leaf class with
  high confidence even for completely unrelated images. MobileNet was
  trained on 1000 real-world ImageNet classes and actually knows what a
  chair looks like, so it can reliably reject non-plant images.

Input  : raw image bytes (JPEG / PNG / WebP)
Output : structured dict — see predict_from_bytes() docstring
"""
from __future__ import annotations

import io
import logging
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import numpy as np

logger = logging.getLogger(__name__)

# ---------------------------------------------------------------------------
# 38-class PlantVillage label order (alphabetical, matches flow_from_directory)
# ---------------------------------------------------------------------------
DISEASE_LABELS: list[str] = [
    "Apple___Apple_scab",
    "Apple___Black_rot",
    "Apple___Cedar_apple_rust",
    "Apple___healthy",
    "Blueberry___healthy",
    "Cherry_(including_sour)___Powdery_mildew",
    "Cherry_(including_sour)___healthy",
    "Corn_(maize)___Cercospora_leaf_spot Gray_leaf_spot",
    "Corn_(maize)___Common_rust_",
    "Corn_(maize)___Northern_Leaf_Blight",
    "Corn_(maize)___healthy",
    "Grape___Black_rot",
    "Grape___Esca_(Black_Measles)",
    "Grape___Leaf_blight_(Isariopsis_Leaf_Spot)",
    "Grape___healthy",
    "Orange___Haunglongbing_(Citrus_greening)",
    "Peach___Bacterial_spot",
    "Peach___healthy",
    "Pepper,_bell___Bacterial_spot",
    "Pepper,_bell___healthy",
    "Potato___Early_blight",
    "Potato___Late_blight",
    "Potato___healthy",
    "Raspberry___healthy",
    "Soybean___healthy",
    "Squash___Powdery_mildew",
    "Strawberry___Leaf_scorch",
    "Strawberry___healthy",
    "Tomato___Bacterial_spot",
    "Tomato___Early_blight",
    "Tomato___Late_blight",
    "Tomato___Leaf_Mold",
    "Tomato___Septoria_leaf_spot",
    "Tomato___Spider_mites Two-spotted_spider_mite",
    "Tomato___Target_Spot",
    "Tomato___Tomato_Yellow_Leaf_Curl_Virus",
    "Tomato___Tomato_mosaic_virus",
    "Tomato___healthy",
]

DISEASE_DISPLAY: dict[str, str] = {
    "Apple___Apple_scab":                                        "Apple Scab",
    "Apple___Black_rot":                                         "Apple Black Rot",
    "Apple___Cedar_apple_rust":                                  "Apple Cedar Rust",
    "Apple___healthy":                                           "Apple (Healthy)",
    "Blueberry___healthy":                                       "Blueberry (Healthy)",
    "Cherry_(including_sour)___Powdery_mildew":                  "Cherry Powdery Mildew",
    "Cherry_(including_sour)___healthy":                         "Cherry (Healthy)",
    "Corn_(maize)___Cercospora_leaf_spot Gray_leaf_spot":        "Corn Gray Leaf Spot",
    "Corn_(maize)___Common_rust_":                               "Corn Common Rust",
    "Corn_(maize)___Northern_Leaf_Blight":                       "Corn Northern Leaf Blight",
    "Corn_(maize)___healthy":                                    "Corn (Healthy)",
    "Grape___Black_rot":                                         "Grape Black Rot",
    "Grape___Esca_(Black_Measles)":                              "Grape Esca (Black Measles)",
    "Grape___Leaf_blight_(Isariopsis_Leaf_Spot)":                "Grape Leaf Blight",
    "Grape___healthy":                                           "Grape (Healthy)",
    "Orange___Haunglongbing_(Citrus_greening)":                  "Orange Citrus Greening (HLB)",
    "Peach___Bacterial_spot":                                    "Peach Bacterial Spot",
    "Peach___healthy":                                           "Peach (Healthy)",
    "Pepper,_bell___Bacterial_spot":                             "Bell Pepper Bacterial Spot",
    "Pepper,_bell___healthy":                                    "Bell Pepper (Healthy)",
    "Potato___Early_blight":                                     "Potato Early Blight",
    "Potato___Late_blight":                                      "Potato Late Blight",
    "Potato___healthy":                                          "Potato (Healthy)",
    "Raspberry___healthy":                                       "Raspberry (Healthy)",
    "Soybean___healthy":                                         "Soybean (Healthy)",
    "Squash___Powdery_mildew":                                   "Squash Powdery Mildew",
    "Strawberry___Leaf_scorch":                                  "Strawberry Leaf Scorch",
    "Strawberry___healthy":                                      "Strawberry (Healthy)",
    "Tomato___Bacterial_spot":                                   "Tomato Bacterial Spot",
    "Tomato___Early_blight":                                     "Tomato Early Blight",
    "Tomato___Late_blight":                                      "Tomato Late Blight",
    "Tomato___Leaf_Mold":                                        "Tomato Leaf Mold",
    "Tomato___Septoria_leaf_spot":                               "Tomato Septoria Leaf Spot",
    "Tomato___Spider_mites Two-spotted_spider_mite":             "Tomato Spider Mites",
    "Tomato___Target_Spot":                                      "Tomato Target Spot",
    "Tomato___Tomato_Yellow_Leaf_Curl_Virus":                    "Tomato Yellow Leaf Curl Virus",
    "Tomato___Tomato_mosaic_virus":                              "Tomato Mosaic Virus",
    "Tomato___healthy":                                          "Tomato (Healthy)",
}

# ---------------------------------------------------------------------------
# Lazy-import TensorFlow / tf_keras
# The model was saved with Keras 2 (tf.keras), so we use tf_keras for
# compatibility with TensorFlow 2.16+ which ships Keras 3 by default.
# ---------------------------------------------------------------------------
try:
    import tensorflow as tf  # noqa: F401
    import tf_keras as keras
    from tf_keras.applications.vgg16 import preprocess_input
    _TF_AVAILABLE = True
except Exception:
    tf = None           # type: ignore[assignment]
    keras = None        # type: ignore[assignment]
    _TF_AVAILABLE = False


class DiseaseModelService:
    """
    Two-stage plant disease classifier.

    Stage 1: MobileNetV2 (ImageNet) — rejects non-plant images.
    Stage 2: VGG16 fine-tuned on PlantVillage — classifies the disease.
    """

    MODEL_NAME    = "vgg16-plant-disease"
    MODEL_VERSION = "1.0.0"
    INPUT_SIZE    = (224, 224)
    TOP_K         = 5

    def __init__(self, model_path: str | Path) -> None:
        self.model_path  = Path(model_path)
        self.model: Any  = None
        self.load_error: Exception | None = None
        self.loaded_at: datetime | None   = None
        self._load()

    # ------------------------------------------------------------------
    # Loading
    # ------------------------------------------------------------------

    def _load(self) -> None:
        if not _TF_AVAILABLE:
            self.load_error = RuntimeError(
                "TensorFlow / tf_keras is not installed. "
                "Install tensorflow and tf-keras to enable disease detection."
            )
            return

        if not self.model_path.exists():
            self.load_error = FileNotFoundError(
                f"VGG16 model file not found: {self.model_path}"
            )
            return

        try:
            self.model     = keras.models.load_model(str(self.model_path))
            self.loaded_at = datetime.now(timezone.utc)
            self.load_error = None
            logger.info("VGG16 disease model loaded from %s", self.model_path)
        except Exception as exc:
            self.model      = None
            self.load_error = exc
            logger.error("Failed to load VGG16 model: %s", exc)

    def is_loaded(self) -> bool:
        return self.model is not None and self.load_error is None

    # ------------------------------------------------------------------
    # Public inference entry point
    # ------------------------------------------------------------------

    def predict_from_bytes(self, image_bytes: bytes) -> dict:
        """
        Run the two-stage pipeline on raw image bytes.

        Returns
        -------
        On SUCCESS (plant image, disease identified):
            {
                "status":       "success",
                "disease":      "Tomato___Late_blight",
                "display_name": "Tomato Late Blight",
                "confidence":   0.97,
                "predictions":  [ {disease, display_name, confidence}, ... ]
            }

        On INVALID (not a plant image — MobileNet rejected it):
            {
                "status":      "invalid",
                "message":     "This image does not appear to be a plant leaf.
                                MobileNet identified it as 'chair' (94.2%).
                                Please upload a clear photo of a crop leaf.",
                "confidence":  0.0,
                "predictions": []
            }
        """
        if not self.is_loaded():
            raise RuntimeError(f"Disease model is not loaded: {self.load_error}")

        # ── Stage 1: MobileNetV2 plant pre-check ─────────────────────────────
        # Import here so the module still loads even if torch is absent
        # (though torch IS in requirements.txt).
        from ..utils.plant_filter import check_is_plant

        try:
            is_plant, mobilenet_label, mobilenet_conf = check_is_plant(image_bytes)
        except Exception as exc:
            # If the filter itself crashes, log and fail open so the disease
            # model still runs — better to show a result than a blank error.
            logger.warning("plant_filter raised an exception (failing open): %s", exc)
            is_plant = True
            mobilenet_label = "unknown"
            mobilenet_conf  = 0.0

        # ── HARD STOP — not a plant ───────────────────────────────────────────
        if not is_plant:
            logger.info(
                "plant_filter REJECTED image: top_label='%s' conf=%.1f%%",
                mobilenet_label, mobilenet_conf * 100,
            )
            return {
                "status":      "invalid",
                "message": (
                    f"This image does not appear to be a plant leaf. "
                    f"MobileNet identified it as \"{mobilenet_label}\" "
                    f"({round(mobilenet_conf * 100, 1)}%). "
                    f"Please upload a clear photo of a crop leaf or plant part."
                ),
                "confidence":  round(mobilenet_conf, 4),
                "predictions": [],
            }

        # ── Stage 2: VGG16 disease classification ────────────────────────────
        return self._run_vgg16(image_bytes)

    # ------------------------------------------------------------------
    # Private: VGG16 inference
    # ------------------------------------------------------------------

    def _run_vgg16(self, image_bytes: bytes) -> dict:
        """Run VGG16 on pre-validated plant image bytes."""
        from PIL import Image as PILImage

        pil_img = PILImage.open(io.BytesIO(image_bytes)).convert("RGB")
        pil_img = pil_img.resize(self.INPUT_SIZE, PILImage.LANCZOS)

        arr = np.array(pil_img, dtype=np.float32)   # (224, 224, 3)
        arr = np.expand_dims(arr, axis=0)            # (1, 224, 224, 3)
        arr = preprocess_input(arr)                  # VGG16 mean subtraction

        preds = self.model.predict(arr, verbose=0)[0]  # shape: (38,)

        top_confidence: float = float(np.max(preds))
        top_index: int        = int(np.argmax(preds))
        top_label: str        = (
            DISEASE_LABELS[top_index]
            if top_index < len(DISEASE_LABELS)
            else f"class_{top_index}"
        )

        logger.info(
            "VGG16 prediction: label=%s  confidence=%.4f",
            top_label, top_confidence,
        )

        # Build top-K list
        top_indices = np.argsort(preds)[::-1][: self.TOP_K]
        predictions = []
        for idx in top_indices:
            lbl = DISEASE_LABELS[idx] if idx < len(DISEASE_LABELS) else f"class_{idx}"
            predictions.append({
                "disease":      lbl,
                "display_name": DISEASE_DISPLAY.get(
                    lbl, lbl.replace("___", " - ").replace("_", " ")
                ),
                "confidence": round(float(preds[idx]), 6),
            })

        display_name = DISEASE_DISPLAY.get(
            top_label, top_label.replace("___", " - ").replace("_", " ")
        )

        return {
            "status":       "success",
            "disease":      top_label,
            "display_name": display_name,
            "confidence":   round(top_confidence, 6),
            "predictions":  predictions,
        }

    # ------------------------------------------------------------------
    # Status / info
    # ------------------------------------------------------------------

    def status(self) -> dict:
        return {
            "loaded":      self.is_loaded(),
            "model_path":  str(self.model_path),
            "model_format": "keras_h5",
            "loaded_at":   self.loaded_at.isoformat() if self.loaded_at else None,
            "error":       str(self.load_error) if self.load_error else None,
            "tf_available": _TF_AVAILABLE,
        }

    def info(self) -> dict:
        return {
            **self.status(),
            "model_name":    self.MODEL_NAME,
            "model_version": self.MODEL_VERSION,
            "input_size":    list(self.INPUT_SIZE),
            "num_classes":   len(DISEASE_LABELS),
            "classes":       DISEASE_LABELS,
            "filter":        "MobileNetV2 (ImageNet) pre-check",
        }
