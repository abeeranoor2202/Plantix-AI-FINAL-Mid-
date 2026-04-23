from __future__ import annotations

from datetime import datetime, timezone
from uuid import uuid4

from flask import Request

from ..schemas.fertilizer import FertilizerPredictionInput


class FertilizerPredictionService:
    def __init__(self, model_service, config):
        self.model_service = model_service
        self.config = config

    def predict(self, payload: dict, request: Request) -> dict:
        validated = FertilizerPredictionInput.model_validate(payload)
        prediction, confidence = self.model_service.predict(validated.to_feature_vector())
        request_id = request.headers.get("X-Request-Id") or str(uuid4())
        created_at = datetime.now(timezone.utc).isoformat()

        return {
            "success": True,
            "fertilizer": prediction,
            "confidence": confidence,
            "request_id": request_id,
            "timestamp": created_at,
            "features": validated.to_feature_dict(),
            "model_name": self.config["FERTILIZER_MODEL_NAME"],
            "model_version": self.config["FERTILIZER_MODEL_VERSION"],
        }
