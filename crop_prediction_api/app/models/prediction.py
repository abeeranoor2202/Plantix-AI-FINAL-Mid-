from __future__ import annotations

from dataclasses import asdict, dataclass
from typing import Any


@dataclass(slots=True)
class PredictionRecord:
    request_id: str
    prediction: str
    confidence: float | None
    features_json: str
    model_name: str
    model_version: str
    created_at: str
    client_ip: str | None = None
    user_agent: str | None = None
    id: int | None = None

    def to_dict(self) -> dict[str, Any]:
        return asdict(self)
