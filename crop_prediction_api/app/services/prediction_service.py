from __future__ import annotations

import json
from datetime import datetime, timezone
from uuid import uuid4

from flask import Request
from pydantic import ValidationError

from ..models.prediction import PredictionRecord
from ..schemas.prediction import CropPredictionInput

# Minimum confidence required to return a crop recommendation.
CONFIDENCE_THRESHOLD = 0.60


class PredictionService:
    def __init__(self, model_service, prediction_repository, config):
        self.model_service = model_service
        self.prediction_repository = prediction_repository
        self.config = config

    def predict(self, payload: dict, request: Request) -> tuple[dict, int]:
        # ── STEP 1: Strict validation ────────────────────────────────────────
        try:
            validated = CropPredictionInput.model_validate(payload)
        except ValidationError:
            return (
                {
                    "status": "invalid",
                    "message": "Invalid input. Only non-negative integers are allowed (pH can be decimal).",
                },
                400,
            )

        # ── STEP 2: Model prediction ─────────────────────────────────────────
        prediction, confidence = self.model_service.predict(validated.to_feature_vector())

        # ── STEP 3: Confidence check ─────────────────────────────────────────
        # If confidence is None (model doesn't support probability) treat as 0.
        effective_confidence = confidence if confidence is not None else 0.0

        if effective_confidence < CONFIDENCE_THRESHOLD:
            return (
                {
                    "status": "low_confidence",
                    "message": "Unable to confidently recommend a crop. Please verify your input values.",
                },
                200,
            )

        # ── STEP 4: Success — log and return ─────────────────────────────────
        features = validated.to_feature_dict()
        request_id = request.headers.get("X-Request-Id") or str(uuid4())
        created_at = datetime.now(timezone.utc).isoformat()
        client_ip = self._resolve_client_ip(request)
        user_agent = request.headers.get("User-Agent")

        record = PredictionRecord(
            request_id=request_id,
            prediction=prediction,
            confidence=effective_confidence,
            features_json=json.dumps(features, ensure_ascii=False),
            model_name=self.config["MODEL_NAME"],
            model_version=self.config["MODEL_VERSION"],
            created_at=created_at,
            client_ip=client_ip,
            user_agent=user_agent,
        )
        self.prediction_repository.log_prediction(record)

        return (
            {
                "status": "success",
                "data": {
                    "crop": prediction,
                    "confidence": round(effective_confidence, 6),
                    "inputs": features,
                },
            },
            200,
        )

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
