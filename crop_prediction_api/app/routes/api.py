from __future__ import annotations

from datetime import datetime, timezone
from uuid import uuid4

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


@api_bp.get("/fertilizer/model-info")
def fertilizer_model_info():
    model_service = current_app.extensions["fertilizer_model_service"]
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


@api_bp.post("/fertilizer/predict")
@require_api_key
@limiter.limit("60 per minute")
def predict_fertilizer():
    payload = request.get_json(silent=True)
    if payload is None:
        raise APIError("Request body must be valid JSON.", status_code=400, error_code="invalid_json")

    fertilizer_prediction_service = current_app.extensions["fertilizer_prediction_service"]
    result = fertilizer_prediction_service.predict(payload, request)
    return jsonify(result), 200


# ---------------------------------------------------------------------------
# Disease Detection  POST /disease/predict
# ---------------------------------------------------------------------------
# Accepts a multipart/form-data upload with field name "image".
# Returns top-5 predictions from the VGG16 plant-disease model.
#
# Example (curl):
#   curl -X POST http://localhost:5000/disease/predict \
#        -H "X-API-Key: your-key" \
#        -F "image=@leaf.jpg"
# ---------------------------------------------------------------------------

ALLOWED_MIME_TYPES = {"image/jpeg", "image/png", "image/webp"}
MAX_IMAGE_BYTES = 10 * 1024 * 1024  # 10 MB


@api_bp.post("/disease/predict")
@require_api_key
@limiter.limit("30 per minute")
def disease_predict():
    disease_model_service = current_app.extensions.get("disease_model_service")
    if disease_model_service is None:
        raise APIError(
            "Disease detection model is not configured.",
            status_code=503,
            error_code="model_unavailable",
        )

    if not disease_model_service.is_loaded():
        raise APIError(
            "Disease detection model failed to load: " + str(disease_model_service.load_error),
            status_code=503,
            error_code="model_not_loaded",
        )

    if "image" not in request.files:
        raise APIError("No image file provided. Send a multipart/form-data request with field 'image'.", status_code=400, error_code="missing_image")

    file = request.files["image"]
    if file.filename == "":
        raise APIError("Empty filename.", status_code=400, error_code="empty_filename")

    mime_type = file.content_type or ""
    if mime_type not in ALLOWED_MIME_TYPES:
        raise APIError(
            f"Unsupported image type '{mime_type}'. Allowed: jpeg, png, webp.",
            status_code=415,
            error_code="unsupported_media_type",
        )

    image_bytes = file.read()
    if len(image_bytes) > MAX_IMAGE_BYTES:
        raise APIError("Image exceeds 10 MB limit.", status_code=413, error_code="image_too_large")
    if len(image_bytes) == 0:
        raise APIError("Empty image file.", status_code=400, error_code="empty_image")

    try:
        result = disease_model_service.predict_from_bytes(image_bytes)
    except Exception as exc:
        current_app.logger.error("Disease inference error: %s", exc)
        raise APIError("Inference failed. Please try again.", status_code=500, error_code="inference_error") from exc

    request_id = request.headers.get("X-Request-Id") or str(uuid4())
    timestamp  = datetime.now(timezone.utc).isoformat()

    # ── Invalid image (below confidence threshold) ────────────────────────────
    # Return HTTP 200 with status="invalid" so the client can show the message.
    # Never include disease name or treatment info in this branch.
    if result.get("status") == "invalid":
        return jsonify(
            {
                "success": True,
                "status": "invalid",
                "message": result["message"],
                "confidence": result["confidence"],
                "predictions": result.get("predictions", []),
                "model": disease_model_service.MODEL_NAME,
                "model_version": disease_model_service.MODEL_VERSION,
                "request_id": request_id,
                "timestamp": timestamp,
            }
        ), 200

    # ── Valid plant leaf — return full prediction ─────────────────────────────
    return jsonify(
        {
            "success": True,
            "status": "success",
            "disease": result["disease"],
            "display_name": result["display_name"],
            "confidence": result["confidence"],
            "predictions": result["predictions"],
            "model": disease_model_service.MODEL_NAME,
            "model_version": disease_model_service.MODEL_VERSION,
            "request_id": request_id,
            "timestamp": timestamp,
        }
    ), 200


@api_bp.get("/disease/model-info")
def disease_model_info():
    disease_model_service = current_app.extensions.get("disease_model_service")
    if disease_model_service is None:
        return jsonify({"success": False, "error": "Disease model not configured."}), 503
    return jsonify({"success": True, **disease_model_service.info()}), 200
