from __future__ import annotations

import logging
import os
from pathlib import Path

from dotenv import load_dotenv
from flask import Flask
from flask_cors import CORS
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address

load_dotenv()


def create_app() -> Flask:
    app = Flask(__name__)

    # ── Config ────────────────────────────────────────────────────────────────
    app.config["SECRET_KEY"]      = os.getenv("SECRET_KEY", "change-me")
    app.config["API_KEY"]         = os.getenv("API_KEY", "")
    app.config["API_KEY_HEADER"]  = os.getenv("API_KEY_HEADER", "X-API-Key")
    app.config["MODEL_NAME"]      = os.getenv("MODEL_NAME", "fertilizer_recommendation_model")
    app.config["MODEL_VERSION"]   = os.getenv("MODEL_VERSION", "1.0.0")
    app.config["MODEL_PATH"]      = Path(os.getenv("MODEL_PATH", "./model/fertilizer.pkl")).resolve()
    app.config["ALLOWED_ORIGINS"] = [o.strip() for o in os.getenv("ALLOWED_ORIGINS", "*").split(",") if o.strip()]
    app.config["RATELIMIT_STORAGE_URI"] = os.getenv("RATELIMIT_STORAGE_URI", "memory://")
    app.json.sort_keys = False

    # ── Logging ───────────────────────────────────────────────────────────────
    log_level = getattr(logging, os.getenv("LOG_LEVEL", "INFO").upper(), logging.INFO)
    logging.basicConfig(
        level=log_level,
        format="%(asctime)s | %(levelname)s | %(name)s | %(message)s",
    )

    # ── Extensions ────────────────────────────────────────────────────────────
    CORS(app, resources={r"/*": {"origins": app.config["ALLOWED_ORIGINS"]}})

    limiter = Limiter(
        get_remote_address,
        app=app,
        default_limits=[os.getenv("RATELIMIT_DEFAULT", "120 per minute")],
        storage_uri=app.config["RATELIMIT_STORAGE_URI"],
    )

    # ── Model ─────────────────────────────────────────────────────────────────
    from .model import FertilizerModel
    model = FertilizerModel(app.config["MODEL_PATH"])
    app.extensions["fertilizer_model"] = model

    if not model.is_loaded():
        app.logger.error("Fertilizer model failed to load: %s", model.load_error)
    else:
        app.logger.info("Fertilizer model loaded from %s", app.config["MODEL_PATH"])

    # ── Routes ────────────────────────────────────────────────────────────────
    from .routes import bp
    app.register_blueprint(bp)

    # ── Error handlers ────────────────────────────────────────────────────────
    from .errors import register_error_handlers
    register_error_handlers(app)

    return app
