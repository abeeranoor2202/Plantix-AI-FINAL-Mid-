from __future__ import annotations

from datetime import datetime, timezone

from flask import Blueprint, current_app, jsonify, request

from ..errors import APIError
from ..extensions import limiter
from ..utils.auth import require_api_key

api_bp = Blueprint("api", __name__)


@api_bp.get("/health")
def health():
    model_service = current_app.extensions["model_service"]
    prediction_service = current_app.extensions["prediction_service"]
    model_status = model_service.status()
    service_health = prediction_service.health()

    return (
        jsonify(
            {
                "success": True,
                "status": "ok",
                **service_health,
                **model_status,
                "timestamp": datetime.now(timezone.utc).isoformat(),
            }
        ),
        200,
    )


@api_bp.get("/model-info")
def model_info():
    model_service = current_app.extensions["model_service"]
    return jsonify({"success": True, **model_service.info()}), 200


@api_bp.post("/predict")
@require_api_key
@limiter.limit("60 per minute")
def predict():
    payload = request.get_json(silent=True)
    if payload is None:
        raise APIError("Request body must be valid JSON.", status_code=400, error_code="invalid_json")

    prediction_service = current_app.extensions["prediction_service"]
    result = prediction_service.predict(payload, request)
    return jsonify(result), 200
