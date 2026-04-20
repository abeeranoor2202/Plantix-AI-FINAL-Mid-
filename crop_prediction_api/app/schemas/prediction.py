from __future__ import annotations

import math
from typing import Any

from pydantic import AliasChoices, BaseModel, ConfigDict, Field, field_validator


class CropPredictionInput(BaseModel):
    model_config = ConfigDict(extra="forbid", populate_by_name=True)

    nitrogen: float = Field(..., ge=0.0, le=500.0, description="Nitrogen content")
    phosphorus: float = Field(
        ..., ge=0.0, le=500.0, validation_alias=AliasChoices("phosphorus", "phosphorous"), description="Phosphorus content"
    )
    potassium: float = Field(
        ..., ge=0.0, le=500.0, validation_alias=AliasChoices("potassium", "pottasium"), description="Potassium content"
    )
    temperature: float = Field(..., ge=-10.0, le=60.0, description="Temperature in Celsius")
    humidity: float = Field(..., ge=0.0, le=100.0, description="Relative humidity percentage")
    ph: float = Field(..., ge=0.0, le=14.0, validation_alias=AliasChoices("ph", "pH"), description="Soil pH")
    rainfall: float = Field(..., ge=0.0, le=5000.0, description="Rainfall in mm")

    @field_validator("nitrogen", "phosphorus", "potassium", "temperature", "humidity", "ph", "rainfall")
    @classmethod
    def finite_number(cls, value: float) -> float:
        if not math.isfinite(float(value)):
            raise ValueError("must be a finite number")
        return float(value)

    def to_feature_vector(self) -> list[float]:
        return [
            float(self.nitrogen),
            float(self.phosphorus),
            float(self.potassium),
            float(self.temperature),
            float(self.humidity),
            float(self.ph),
            float(self.rainfall),
        ]

    def to_feature_dict(self) -> dict[str, float]:
        return {
            "nitrogen": float(self.nitrogen),
            "phosphorus": float(self.phosphorus),
            "potassium": float(self.potassium),
            "temperature": float(self.temperature),
            "humidity": float(self.humidity),
            "ph": float(self.ph),
            "rainfall": float(self.rainfall),
        }
