from __future__ import annotations

from datetime import datetime, timezone
from uuid import uuid4

from flask import Blueprint, current_app, jsonify, request
from pydantic import ValidationError

from .auth import require_api_key
from .errors import APIError, ModelError
from .schema import FertilizerInput

bp = Blueprint("fertilizer", __name__)


@bp.get("/health")
def health():
    model = current_app.extensions["fertilizer_model"]
    return jsonify({
        "success": True,
        "status": "ok",
        "model_loaded": model.is_loaded(),
        "timestamp": datetime.now(timezone.utc).isoformat(),
    }), 200


@bp.get("/fertilizer/model-info")
def model_info():
    model = current_app.extensions["fertilizer_model"]
    cfg   = current_app.config
    return jsonify({
        "success": True,
        "model_name": cfg["MODEL_NAME"],
        "model_version": cfg["MODEL_VERSION"],
        **model.info(),
    }), 200


@bp.post("/fertilizer/predict")
@require_api_key
def predict():
    payload = request.get_json(silent=True)
    if payload is None:
        raise APIError("Request body must be valid JSON.", status_code=400, error_code="invalid_json")

    # ── Validation ────────────────────────────────────────────────────────────
    try:
        validated = FertilizerInput.model_validate(payload)
    except ValidationError:
        return jsonify({
            "status": "invalid",
            "message": "Invalid input. Only non-negative integers are allowed for nitrogen, potassium, and phosphorous.",
        }), 400

    # ── Prediction ────────────────────────────────────────────────────────────
    model = current_app.extensions["fertilizer_model"]
    if not model.is_loaded():
        raise ModelError(str(model.load_error) if model.load_error else "Model not loaded.")

    try:
        fertilizer, confidence = model.predict(validated.to_feature_vector())
    except Exception as exc:
        current_app.logger.error("Fertilizer inference error: %s", exc)
        raise ModelError(str(exc)) from exc

    cfg        = current_app.config
    request_id = request.headers.get("X-Request-Id") or str(uuid4())
    timestamp  = datetime.now(timezone.utc).isoformat()

    return jsonify({
        "success": True,
        "fertilizer": fertilizer,
        "confidence": round(confidence, 6) if confidence is not None else None,
        "request_id": request_id,
        "timestamp": timestamp,
        "features": validated.to_feature_dict(),
        "model_name": cfg["MODEL_NAME"],
        "model_version": cfg["MODEL_VERSION"],
    }), 200
