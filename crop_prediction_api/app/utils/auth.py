from __future__ import annotations

import hmac
from functools import wraps

from flask import current_app, request

from ..errors import AuthenticationError


def require_api_key(view_func):
    @wraps(view_func)
    def wrapped(*args, **kwargs):
        expected_key = current_app.config.get("API_KEY", "")
        provided_key = request.headers.get(current_app.config.get("API_KEY_HEADER", "X-API-Key"), "")

        if not expected_key or not hmac.compare_digest(provided_key, expected_key):
            raise AuthenticationError()

        return view_func(*args, **kwargs)

    return wrapped
