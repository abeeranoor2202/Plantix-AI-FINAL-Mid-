from __future__ import annotations

from flask import Blueprint, current_app, jsonify, request

from ..extensions import limiter
from ..utils.auth import require_api_key

admin_bp = Blueprint("admin", __name__, url_prefix="/admin")


@admin_bp.get("/predictions")
@require_api_key
@limiter.limit("120 per minute")
def predictions_history():
    prediction_service = current_app.extensions["prediction_service"]
    limit = _clamp_int(request.args.get("limit", 50), minimum=1, maximum=200)
    offset = _clamp_int(request.args.get("offset", 0), minimum=0, maximum=1_000_000)
    items = prediction_service.list_predictions(limit=limit, offset=offset)

    return (
        jsonify(
            {
                "success": True,
                "count": len(items),
                "limit": limit,
                "offset": offset,
                "items": items,
            }
        ),
        200,
    )


@admin_bp.get("/stats")
@require_api_key
@limiter.limit("60 per minute")
def stats():
    prediction_service = current_app.extensions["prediction_service"]
    return jsonify({"success": True, "stats": prediction_service.get_stats()}), 200


def _clamp_int(raw_value, minimum: int, maximum: int) -> int:
    try:
        value = int(raw_value)
    except (TypeError, ValueError):
        value = minimum
    return max(minimum, min(maximum, value))
