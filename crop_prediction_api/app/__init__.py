from __future__ import annotations

from flask import Flask, request

from .config import get_config
from .errors import register_error_handlers
from .extensions import cors, limiter
from .routes.admin import admin_bp
from .routes.api import api_bp
from .services.model_service import ModelService
from .services.prediction_service import PredictionService
from .utils.db import PredictionRepository
from .utils.logging import configure_logging


def create_app(config_name: str | None = None) -> Flask:
    app = Flask(__name__, instance_relative_config=True)
    app.config.from_object(get_config(config_name))
    app.json.sort_keys = False

    configure_logging(app)
    cors.init_app(app, resources={r"/*": {"origins": app.config["ALLOWED_ORIGINS"]}})
    limiter.init_app(app)

    prediction_repository = PredictionRepository(app.config["DATABASE_PATH"])
    prediction_repository.init_db()

    model_service = ModelService(
        model_path=app.config["MODEL_PATH"],
        feature_order=app.config["FEATURE_ORDER"],
        model_name=app.config["MODEL_NAME"],
        model_version=app.config["MODEL_VERSION"],
    )
    prediction_service = PredictionService(model_service, prediction_repository, app.config)

    app.extensions["prediction_repository"] = prediction_repository
    app.extensions["model_service"] = model_service
    app.extensions["prediction_service"] = prediction_service

    register_error_handlers(app)

    app.register_blueprint(api_bp)
    app.register_blueprint(admin_bp)

    @app.before_request
    def capture_request():
        app.logger.debug("%s %s", request.method, request.path)

    return app
