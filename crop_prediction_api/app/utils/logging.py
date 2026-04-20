from __future__ import annotations

import logging
from logging.handlers import RotatingFileHandler
from pathlib import Path


def configure_logging(app):
    log_level = getattr(logging, app.config.get("LOG_LEVEL", "INFO").upper(), logging.INFO)
    formatter = logging.Formatter("%(asctime)s | %(levelname)s | %(name)s | %(message)s")

    root_logger = logging.getLogger()
    root_logger.setLevel(log_level)

    for handler in list(root_logger.handlers):
        root_logger.removeHandler(handler)

    stream_handler = logging.StreamHandler()
    stream_handler.setFormatter(formatter)
    root_logger.addHandler(stream_handler)

    instance_path = Path(app.instance_path)
    instance_path.mkdir(parents=True, exist_ok=True)
    file_handler = RotatingFileHandler(instance_path / "api.log", maxBytes=1_048_576, backupCount=5)
    file_handler.setFormatter(formatter)
    root_logger.addHandler(file_handler)

    app.logger.handlers = root_logger.handlers
    app.logger.setLevel(log_level)
    app.logger.propagate = False
