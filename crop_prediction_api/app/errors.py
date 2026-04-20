from __future__ import annotations

from http import HTTPStatus

from flask import jsonify
from pydantic import ValidationError
from werkzeug.exceptions import BadRequest, HTTPException, RequestEntityTooLarge


class APIError(Exception):
    def __init__(self, message: str, status_code: int = 400, error_code: str = "bad_request", details=None):
        super().__init__(message)
        self.message = message
        self.status_code = status_code
        self.error_code = error_code
        self.details = details


class AuthenticationError(APIError):
    def __init__(self, message: str = "Invalid or missing API key."):
        super().__init__(message, status_code=401, error_code="authentication_failed")


class ModelNotLoadedError(APIError):
    def __init__(self, message: str = "Model is not available."):
        super().__init__(message, status_code=503, error_code="model_unavailable")


class DatabaseError(APIError):
    def __init__(self, message: str = "Database operation failed."):
        super().__init__(message, status_code=500, error_code="database_error")


def _validation_errors_payload(error: ValidationError):
    items = []
    for item in error.errors():
        field = ".".join(str(part) for part in item.get("loc", []))
        items.append({"field": field, "message": item.get("msg", "Invalid value")})
    return items


def register_error_handlers(app):
    @app.errorhandler(APIError)
    def handle_api_error(error: APIError):
        payload = {
            "success": False,
            "error": error.error_code,
            "message": error.message,
        }
        if error.details is not None:
            payload["details"] = error.details
        return jsonify(payload), error.status_code

    @app.errorhandler(ValidationError)
    def handle_validation_error(error: ValidationError):
        return (
            jsonify(
                {
                    "success": False,
                    "error": "validation_error",
                    "message": "Input validation failed.",
                    "errors": _validation_errors_payload(error),
                }
            ),
            422,
        )

    @app.errorhandler(BadRequest)
    def handle_bad_request(error: BadRequest):
        return jsonify({"success": False, "error": "bad_request", "message": error.description}), 400

    @app.errorhandler(RequestEntityTooLarge)
    def handle_payload_too_large(error: RequestEntityTooLarge):
        return jsonify({"success": False, "error": "payload_too_large", "message": "Request body is too large."}), 413

    @app.errorhandler(HTTPException)
    def handle_http_exception(error: HTTPException):
        return jsonify({"success": False, "error": error.name.lower().replace(" ", "_"), "message": error.description}), error.code or 500

    @app.errorhandler(Exception)
    def handle_unexpected_exception(error: Exception):
        app.logger.exception("Unhandled exception")
        return (
            jsonify(
                {
                    "success": False,
                    "error": "internal_server_error",
                    "message": "An unexpected server error occurred.",
                }
            ),
            HTTPStatus.INTERNAL_SERVER_ERROR,
        )
