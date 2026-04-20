from __future__ import annotations

from flask import jsonify


def success_response(payload: dict | None = None, status_code: int = 200):
    body = {"success": True}
    if payload:
        body.update(payload)
    return jsonify(body), status_code


def error_response(message: str, status_code: int = 400, error_code: str = "bad_request", details=None):
    body = {"success": False, "error": error_code, "message": message}
    if details is not None:
        body["details"] = details
    return jsonify(body), status_code
