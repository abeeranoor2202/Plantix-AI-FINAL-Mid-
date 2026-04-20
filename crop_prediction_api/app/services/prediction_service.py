from __future__ import annotations

import json
from datetime import datetime, timezone
from uuid import uuid4

from flask import Request

from ..models.prediction import PredictionRecord
from ..schemas.prediction import CropPredictionInput


class PredictionService:
    def __init__(self, model_service, prediction_repository, config):
        self.model_service = model_service
        self.prediction_repository = prediction_repository
        self.config = config

    def predict(self, payload: dict, request: Request) -> dict:
        validated = CropPredictionInput.model_validate(payload)
        features = validated.to_feature_dict()
        prediction, confidence = self.model_service.predict(validated.to_feature_vector())
        request_id = request.headers.get("X-Request-Id") or str(uuid4())
        created_at = datetime.now(timezone.utc).isoformat()
        client_ip = self._resolve_client_ip(request)
        user_agent = request.headers.get("User-Agent")

        record = PredictionRecord(
            request_id=request_id,
            prediction=prediction,
            confidence=confidence,
            features_json=json.dumps(features, ensure_ascii=False),
            model_name=self.config["MODEL_NAME"],
            model_version=self.config["MODEL_VERSION"],
            created_at=created_at,
            client_ip=client_ip,
            user_agent=user_agent,
        )
        record_id = self.prediction_repository.log_prediction(record)

        return {
            "success": True,
            "prediction": prediction,
            "confidence": confidence,
            "request_id": request_id,
            "record_id": record_id,
            "timestamp": created_at,
        }

    def list_predictions(self, limit: int, offset: int) -> list[dict]:
        return self.prediction_repository.list_predictions(limit=limit, offset=offset)

    def get_stats(self) -> dict:
        return self.prediction_repository.get_stats()

    def health(self) -> dict:
        return {
            "model_loaded": self.model_service.is_loaded(),
            "database_ready": self.prediction_repository.healthcheck(),
        }

    @staticmethod
    def _resolve_client_ip(request: Request) -> str | None:
        forwarded_for = request.headers.get("X-Forwarded-For", "")
        if forwarded_for:
            candidate = forwarded_for.split(",")[0].strip()
            if candidate:
                return candidate
        return request.remote_addr
