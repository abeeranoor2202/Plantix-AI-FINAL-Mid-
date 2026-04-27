from __future__ import annotations

import functools

from flask import current_app, request

from .errors import AuthError


def require_api_key(f):
    @functools.wraps(f)
    def decorated(*args, **kwargs):
        header_name = current_app.config.get("API_KEY_HEADER", "X-API-Key")
        expected    = current_app.config.get("API_KEY", "")
        provided    = request.headers.get(header_name, "")

        if not expected or not provided or provided != expected:
            raise AuthError()

        return f(*args, **kwargs)
    return decorated
