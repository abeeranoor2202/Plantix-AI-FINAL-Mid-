"""
DiseaseModelService
===================
Loads vgg16Mymodel.h5 (Keras/TensorFlow VGG16 fine-tuned on the
PlantVillage 38-class dataset) and runs image inference.

Input  : raw image bytes (JPEG / PNG / WebP)
Output : list of {"disease": str, "confidence": float} sorted desc
"""
from __future__ import annotations

import io
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import numpy as np

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

# Human-readable display names
DISEASE_DISPLAY: dict[str, str] = {
    "Apple___Apple_scab": "Apple Scab",
    "Apple___Black_rot": "Apple Black Rot",
    "Apple___Cedar_apple_rust": "Apple Cedar Rust",
    "Apple___healthy": "Apple (Healthy)",
    "Blueberry___healthy": "Blueberry (Healthy)",
    "Cherry_(including_sour)___Powdery_mildew": "Cherry Powdery Mildew",
    "Cherry_(including_sour)___healthy": "Cherry (Healthy)",
    "Corn_(maize)___Cercospora_leaf_spot Gray_leaf_spot": "Corn Gray Leaf Spot",
    "Corn_(maize)___Common_rust_": "Corn Common Rust",
    "Corn_(maize)___Northern_Leaf_Blight": "Corn Northern Leaf Blight",
    "Corn_(maize)___healthy": "Corn (Healthy)",
    "Grape___Black_rot": "Grape Black Rot",
    "Grape___Esca_(Black_Measles)": "Grape Esca (Black Measles)",
    "Grape___Leaf_blight_(Isariopsis_Leaf_Spot)": "Grape Leaf Blight",
    "Grape___healthy": "Grape (Healthy)",
    "Orange___Haunglongbing_(Citrus_greening)": "Orange Citrus Greening (HLB)",
    "Peach___Bacterial_spot": "Peach Bacterial Spot",
    "Peach___healthy": "Peach (Healthy)",
    "Pepper,_bell___Bacterial_spot": "Bell Pepper Bacterial Spot",
    "Pepper,_bell___healthy": "Bell Pepper (Healthy)",
    "Potato___Early_blight": "Potato Early Blight",
    "Potato___Late_blight": "Potato Late Blight",
    "Potato___healthy": "Potato (Healthy)",
    "Raspberry___healthy": "Raspberry (Healthy)",
    "Soybean___healthy": "Soybean (Healthy)",
    "Squash___Powdery_mildew": "Squash Powdery Mildew",
    "Strawberry___Leaf_scorch": "Strawberry Leaf Scorch",
    "Strawberry___healthy": "Strawberry (Healthy)",
    "Tomato___Bacterial_spot": "Tomato Bacterial Spot",
    "Tomato___Early_blight": "Tomato Early Blight",
    "Tomato___Late_blight": "Tomato Late Blight",
    "Tomato___Leaf_Mold": "Tomato Leaf Mold",
    "Tomato___Septoria_leaf_spot": "Tomato Septoria Leaf Spot",
    "Tomato___Spider_mites Two-spotted_spider_mite": "Tomato Spider Mites",
    "Tomato___Target_Spot": "Tomato Target Spot",
    "Tomato___Tomato_Yellow_Leaf_Curl_Virus": "Tomato Yellow Leaf Curl Virus",
    "Tomato___Tomato_mosaic_virus": "Tomato Mosaic Virus",
    "Tomato___healthy": "Tomato (Healthy)",
}

# Lazy-import TensorFlow so the rest of the app still starts if TF is absent
try:
    import tensorflow as tf
    from tensorflow.keras.applications.vgg16 import preprocess_input
    from tensorflow.keras.preprocessing import image as keras_image
    _TF_AVAILABLE = True
except Exception:  # pragma: no cover
    tf = None  # type: ignore[assignment]
    _TF_AVAILABLE = False


class DiseaseModelService:
    """Wraps the VGG16 Keras model for plant disease classification."""

    MODEL_NAME = "vgg16-plant-disease"
    MODEL_VERSION = "1.0.0"
    INPUT_SIZE = (224, 224)
    TOP_K = 5  # return top-5 predictions

    def __init__(self, model_path: str | Path) -> None:
        self.model_path = Path(model_path)
        self.model: Any = None
        self.load_error: Exception | None = None
        self.loaded_at: datetime | None = None
        self._load()

    # ------------------------------------------------------------------
    # Loading
    # ------------------------------------------------------------------

    def _load(self) -> None:
        if not _TF_AVAILABLE:
            self.load_error = RuntimeError(
                "TensorFlow is not installed. Install tensorflow to enable disease detection."
            )
            return

        if not self.model_path.exists():
            self.load_error = FileNotFoundError(
                f"VGG16 model file not found: {self.model_path}"
            )
            return

        try:
            self.model = tf.keras.models.load_model(str(self.model_path))
            self.loaded_at = datetime.now(timezone.utc)
            self.load_error = None
        except Exception as exc:
            self.model = None
            self.load_error = exc

    def is_loaded(self) -> bool:
        return self.model is not None and self.load_error is None

    # ------------------------------------------------------------------
    # Inference
    # ------------------------------------------------------------------

    def predict_from_bytes(self, image_bytes: bytes) -> list[dict]:
        """
        Run inference on raw image bytes.

        Returns a list of dicts sorted by confidence descending:
            [{"disease": "Tomato___Late_blight", "display_name": "Tomato Late Blight", "confidence": 0.97}, ...]
        """
        if not self.is_loaded():
            raise RuntimeError(
                f"Disease model is not loaded: {self.load_error}"
            )

        img = keras_image.load_img(
            io.BytesIO(image_bytes), target_size=self.INPUT_SIZE
        )
        arr = keras_image.img_to_array(img)
        arr = np.expand_dims(arr, axis=0)
        arr = preprocess_input(arr)  # VGG16 preprocessing (mean subtraction)

        preds = self.model.predict(arr, verbose=0)[0]  # shape: (38,)

        # Build top-K results
        top_indices = np.argsort(preds)[::-1][: self.TOP_K]
        results = []
        for idx in top_indices:
            label = DISEASE_LABELS[idx] if idx < len(DISEASE_LABELS) else f"class_{idx}"
            results.append(
                {
                    "disease": label,
                    "display_name": DISEASE_DISPLAY.get(label, label.replace("___", " - ").replace("_", " ")),
                    "confidence": round(float(preds[idx]), 6),
                }
            )
        return results

    # ------------------------------------------------------------------
    # Status / info
    # ------------------------------------------------------------------

    def status(self) -> dict:
        return {
            "loaded": self.is_loaded(),
            "model_path": str(self.model_path),
            "model_format": "keras_h5",
            "loaded_at": self.loaded_at.isoformat() if self.loaded_at else None,
            "error": str(self.load_error) if self.load_error else None,
            "tf_available": _TF_AVAILABLE,
        }

    def info(self) -> dict:
        return {
            **self.status(),
            "model_name": self.MODEL_NAME,
            "model_version": self.MODEL_VERSION,
            "input_size": list(self.INPUT_SIZE),
            "num_classes": len(DISEASE_LABELS),
            "classes": DISEASE_LABELS,
        }
