from __future__ import annotations

import math
from typing import Any

from pydantic import AliasChoices, BaseModel, ConfigDict, Field, field_validator

# ---------------------------------------------------------------------------
# Integer-only fields: N, P, K, temperature, humidity, rainfall
# pH is the ONLY field allowed to be a decimal value.
# All values must be non-negative (pH included).
# ---------------------------------------------------------------------------


class CropPredictionInput(BaseModel):
    model_config = ConfigDict(extra="forbid", populate_by_name=True)

    nitrogen: int = Field(..., ge=0, le=500, description="Nitrogen content (non-negative integer)")
    phosphorus: int = Field(
        ..., ge=0, le=500,
        validation_alias=AliasChoices("phosphorus", "phosphorous"),
        description="Phosphorus content (non-negative integer)",
    )
    potassium: int = Field(
        ..., ge=0, le=500,
        validation_alias=AliasChoices("potassium", "pottasium"),
        description="Potassium content (non-negative integer)",
    )
    temperature: int = Field(..., ge=-10, le=60, description="Temperature in Celsius (non-negative integer)")
    humidity: int = Field(..., ge=0, le=100, description="Relative humidity percentage (non-negative integer)")
    # pH is the ONLY decimal-allowed field
    ph: float = Field(
        ..., ge=0.0, le=14.0,
        validation_alias=AliasChoices("ph", "pH"),
        description="Soil pH (non-negative, decimal allowed)",
    )
    rainfall: int = Field(..., ge=0, le=5000, description="Rainfall in mm (non-negative integer)")

    @field_validator("nitrogen", "phosphorus", "potassium", "temperature", "humidity", "rainfall", mode="before")
    @classmethod
    def must_be_integer(cls, value: Any) -> int:
        """Reject floats and non-integers. Caller must send a proper JSON integer."""
        if isinstance(value, bool):
            raise ValueError("must be a non-negative integer, not a boolean")
        if isinstance(value, float):
            raise ValueError("must be a non-negative integer (decimal values are not allowed)")
        if not isinstance(value, int):
            raise ValueError("must be a non-negative integer")
        return value

    @field_validator("ph", mode="before")
    @classmethod
    def ph_must_be_finite_and_non_negative(cls, value: Any) -> float:
        if isinstance(value, bool):
            raise ValueError("must be a non-negative number")
        if not isinstance(value, (int, float)):
            raise ValueError("must be a non-negative number")
        fval = float(value)
        if not math.isfinite(fval):
            raise ValueError("must be a finite number")
        return fval

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

    def to_feature_dict(self) -> dict[str, Any]:
        return {
            "N": self.nitrogen,
            "P": self.phosphorus,
            "K": self.potassium,
            "temperature": self.temperature,
            "humidity": self.humidity,
            "ph": self.ph,
            "rainfall": self.rainfall,
        }
