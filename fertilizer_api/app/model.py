from __future__ import annotations

import pickle
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import joblib
import numpy as np


class FertilizerModel:
    """Loads and runs the fertilizer RandomForest pickle."""

    def __init__(self, model_path: str | Path) -> None:
        self.model_path = Path(model_path)
        self.model: Any = None
        self.load_error: Exception | None = None
        self.loaded_at: datetime | None = None
        self._load()

    def _load(self) -> None:
        if not self.model_path.exists():
            self.load_error = FileNotFoundError(f"Model file not found: {self.model_path}")
            return
        try:
            try:
                self.model = joblib.load(self.model_path)
            except Exception:
                with self.model_path.open("rb") as fh:
                    self.model = pickle.load(fh)
            self.loaded_at = datetime.now(timezone.utc)
        except Exception as exc:
            self.load_error = exc
            self.model = None

    def is_loaded(self) -> bool:
        return self.model is not None and self.load_error is None

    def predict(self, feature_vector: list[float]) -> tuple[str, float | None]:
        if not self.is_loaded():
            raise RuntimeError(str(self.load_error) if self.load_error else "Model not loaded.")
        if not hasattr(self.model, "predict"):
            raise RuntimeError("Loaded model does not expose a predict method.")

        X = np.asarray(feature_vector, dtype=np.float32).reshape(1, -1)
        raw = self.model.predict(X)[0]
        prediction = raw.item() if hasattr(raw, "item") else str(raw)

        confidence: float | None = None
        if hasattr(self.model, "predict_proba"):
            proba = np.asarray(self.model.predict_proba(X), dtype=np.float64)
            if proba.ndim == 2 and proba.shape[1] > 0:
                confidence = float(np.max(proba[0]))

        return str(prediction), confidence

    def info(self) -> dict:
        return {
            "model_path": str(self.model_path),
            "loaded": self.is_loaded(),
            "loaded_at": self.loaded_at.isoformat() if self.loaded_at else None,
            "error": str(self.load_error) if self.load_error else None,
            "supports_predict_proba": self.model is not None and hasattr(self.model, "predict_proba"),
            "classes": (
                self.model.classes_.tolist()
                if self.model is not None and hasattr(self.model, "classes_")
                else None
            ),
        }
