from __future__ import annotations

import pickle
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import joblib
import numpy as np

from ..config import FEATURE_RANGES
from ..errors import ModelNotLoadedError

try:
    import torch
except Exception:  # pragma: no cover - torch is optional for non-torch deployments
    torch = None


class ModelService:
    def __init__(self, model_path: str | Path, feature_order: list[str], model_name: str, model_version: str):
        self.model_path = Path(model_path)
        self.feature_order = list(feature_order)
        self.model_name = model_name
        self.model_version = model_version
        self.model: Any = None
        self.load_error: Exception | None = None
        self.loaded_at: datetime | None = None
        self.model_format = self._detect_format()
        self.load_model()

    def _detect_format(self) -> str:
        suffix = self.model_path.suffix.lower()
        if suffix in {".joblib", ".pkl", ".pickle"}:
            return "joblib/pickle"
        if suffix in {".pt", ".pth"}:
            return "torch"
        return "unknown"

    def load_model(self) -> None:
        if not self.model_path.exists():
            self.load_error = FileNotFoundError(f"Model file not found at {self.model_path}")
            self.model = None
            return

        try:
            suffix = self.model_path.suffix.lower()
            if suffix in {".joblib", ".pkl", ".pickle"}:
                self.model = self._load_pickle_or_joblib()
            elif suffix in {".pt", ".pth"}:
                if torch is None:
                    raise RuntimeError("PyTorch is not installed, so .pth/.pt models cannot be loaded.")
                self.model = torch.load(self.model_path, map_location="cpu")
                if hasattr(self.model, "eval"):
                    self.model.eval()
            else:
                self.model = self._load_pickle_or_joblib()

            self.loaded_at = datetime.now(timezone.utc)
            self.load_error = None
        except Exception as exc:  # pragma: no cover - startup safety path
            self.model = None
            self.load_error = exc

    def _load_pickle_or_joblib(self):
        try:
            return joblib.load(self.model_path)
        except Exception:
            with self.model_path.open("rb") as handle:
                return pickle.load(handle)

    def is_loaded(self) -> bool:
        return self.model is not None and self.load_error is None

    def predict(self, feature_vector: list[float]) -> tuple[str, float | None]:
        if not self.is_loaded():
            raise ModelNotLoadedError(str(self.load_error) if self.load_error else "Model has not been loaded.")

        if not hasattr(self.model, "predict"):
            raise ModelNotLoadedError("Loaded model does not expose a predict method.")

        vector = np.asarray(feature_vector, dtype=np.float32).reshape(1, -1)
        prediction_output = self.model.predict(vector)
        prediction = prediction_output[0]
        if hasattr(prediction, "item"):
            prediction = prediction.item()
        prediction = str(prediction)

        confidence = self._predict_confidence(vector)
        return prediction, confidence

    def _predict_confidence(self, vector: np.ndarray) -> float | None:
        if hasattr(self.model, "predict_proba"):
            probabilities = np.asarray(self.model.predict_proba(vector), dtype=np.float64)
            if probabilities.ndim == 2 and probabilities.shape[1] > 0:
                return float(np.max(probabilities[0]))
            return None

        if hasattr(self.model, "decision_function"):
            scores = np.asarray(self.model.decision_function(vector), dtype=np.float64).reshape(-1)
            if scores.size == 1:
                return float(1.0 / (1.0 + np.exp(-scores[0])))
            shifted = scores - np.max(scores)
            exp_scores = np.exp(shifted)
            probabilities = exp_scores / np.sum(exp_scores)
            return float(np.max(probabilities))

        return None

    def status(self) -> dict[str, Any]:
        return {
            "loaded": self.is_loaded(),
            "model_path": str(self.model_path),
            "model_format": self.model_format,
            "loaded_at": self.loaded_at.isoformat() if self.loaded_at else None,
            "error": str(self.load_error) if self.load_error else None,
        }

    def info(self) -> dict[str, Any]:
        model = self.model
        status = self.status()

        return {
            **status,
            "model_name": self.model_name,
            "model_version": self.model_version,
            "feature_order": self.feature_order,
            "feature_ranges": FEATURE_RANGES,
            "supports_predict_proba": bool(model is not None and hasattr(model, "predict_proba")),
            "classes": self._serialize_attribute(model, "classes_"),
            "feature_names_in": self._serialize_attribute(model, "feature_names_in_"),
            "n_features_in": int(getattr(model, "n_features_in_", 0)) if model is not None and getattr(model, "n_features_in_", None) is not None else None,
        }

    @staticmethod
    def _serialize_attribute(model: Any, attribute_name: str):
        if model is None or not hasattr(model, attribute_name):
            return None
        value = getattr(model, attribute_name)
        if value is None:
            return None
        if hasattr(value, "tolist"):
            return value.tolist()
        if isinstance(value, (list, tuple)):
            return list(value)
        return str(value)
