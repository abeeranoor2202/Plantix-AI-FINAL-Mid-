from __future__ import annotations

from flask import jsonify
from pydantic import ValidationError


class APIError(Exception):
    def __init__(self, message: str, status_code: int = 400, error_code: str = "bad_request"):
        super().__init__(message)
        self.message    = message
        self.status_code = status_code
        self.error_code  = error_code


class AuthError(APIError):
    def __init__(self, message: str = "Invalid or missing API key."):
        super().__init__(message, status_code=401, error_code="authentication_failed")


class ModelError(APIError):
    def __init__(self, message: str = "Model is not available."):
        super().__init__(message, status_code=503, error_code="model_unavailable")


def register_error_handlers(app):
    @app.errorhandler(APIError)
    def handle_api_error(error: APIError):
        return jsonify({"success": False, "error": error.error_code, "message": error.message}), error.status_code

    @app.errorhandler(ValidationError)
    def handle_validation_error(error: ValidationError):
        items = [
            {"field": ".".join(str(p) for p in e.get("loc", [])), "message": e.get("msg", "Invalid value")}
            for e in error.errors()
        ]
        return jsonify({"success": False, "error": "validation_error", "message": "Input validation failed.", "errors": items}), 422

    @app.errorhandler(Exception)
    def handle_unexpected(error: Exception):
        app.logger.exception("Unhandled exception")
        return jsonify({"success": False, "error": "internal_server_error", "message": "An unexpected error occurred."}), 500
