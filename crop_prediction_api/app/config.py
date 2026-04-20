from __future__ import annotations

import os
from pathlib import Path

from dotenv import load_dotenv

load_dotenv()

BASE_DIR = Path(__file__).resolve().parents[1]
DEFAULT_MODEL_PATH = (BASE_DIR / "model" / "RandomForest.pkl").resolve()
DEFAULT_DATABASE_PATH = (BASE_DIR / "instance" / "predictions.sqlite3").resolve()

CROP_FEATURES = [
    "nitrogen",
    "phosphorus",
    "potassium",
    "temperature",
    "humidity",
    "ph",
    "rainfall",
]

FEATURE_RANGES = {
    "nitrogen": (0.0, 500.0),
    "phosphorus": (0.0, 500.0),
    "potassium": (0.0, 500.0),
    "temperature": (-10.0, 60.0),
    "humidity": (0.0, 100.0),
    "ph": (0.0, 14.0),
    "rainfall": (0.0, 5000.0),
}

CROP_MODEL_LABELS = [
    "apple",
    "banana",
    "blackgram",
    "chickpea",
    "coconut",
    "coffee",
    "cotton",
    "grapes",
    "jute",
    "kidneybeans",
    "lentil",
    "maize",
    "mango",
    "mothbeans",
    "mungbean",
    "muskmelon",
    "orange",
    "papaya",
    "pigeonpeas",
    "pomegranate",
    "rice",
    "watermelon",
]


class BaseConfig:
    SECRET_KEY = os.getenv("SECRET_KEY", "change-this-secret-key")
    API_KEY = os.getenv("API_KEY", "change-this-api-key")
    API_KEY_HEADER = os.getenv("API_KEY_HEADER", "X-API-Key")
    MODEL_NAME = os.getenv("MODEL_NAME", "crop_recommendation_model")
    MODEL_VERSION = os.getenv("MODEL_VERSION", "1.0.0")
    MODEL_PATH = Path(os.getenv("MODEL_PATH", str(DEFAULT_MODEL_PATH))).resolve()
    DATABASE_PATH = Path(os.getenv("DATABASE_PATH", str(DEFAULT_DATABASE_PATH))).resolve()
    FEATURE_ORDER = CROP_FEATURES
    FEATURE_RANGES = FEATURE_RANGES
    ALLOWED_ORIGINS = [origin.strip() for origin in os.getenv("ALLOWED_ORIGINS", "*").split(",") if origin.strip()]
    MAX_CONTENT_LENGTH = int(os.getenv("MAX_CONTENT_LENGTH", str(1 * 1024 * 1024)))
    JSON_SORT_KEYS = False
    JSONIFY_PRETTYPRINT_REGULAR = False
    LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO")
    RATELIMIT_DEFAULT = os.getenv("RATELIMIT_DEFAULT", "120 per minute")
    RATELIMIT_STORAGE_URI = os.getenv("RATELIMIT_STORAGE_URI", "memory://")
    RATELIMIT_HEADERS_ENABLED = True
    PREDICTION_HISTORY_LIMIT = int(os.getenv("PREDICTION_HISTORY_LIMIT", "100"))
    MODEL_LABELS = CROP_MODEL_LABELS


class DevelopmentConfig(BaseConfig):
    DEBUG = True


class TestingConfig(BaseConfig):
    TESTING = True
    RATELIMIT_DEFAULT = "1000 per minute"


class ProductionConfig(BaseConfig):
    DEBUG = False


CONFIG_MAP = {
    "development": DevelopmentConfig,
    "testing": TestingConfig,
    "production": ProductionConfig,
    None: BaseConfig,
}


def get_config(name: str | None = None):
    selected = (name or os.getenv("FLASK_ENV") or os.getenv("APP_ENV") or "production").lower()
    return CONFIG_MAP.get(selected, ProductionConfig)
