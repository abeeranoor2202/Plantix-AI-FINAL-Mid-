from __future__ import annotations

import math

from pydantic import AliasChoices, BaseModel, ConfigDict, Field, field_validator


class FertilizerPredictionInput(BaseModel):
    model_config = ConfigDict(extra="forbid", populate_by_name=True)

    nitrogen: float = Field(..., ge=0.0, le=500.0, description="Nitrogen content")
    potassium: float = Field(
        ..., ge=0.0, le=500.0, validation_alias=AliasChoices("potassium", "pottasium"), description="Potassium content"
    )
    phosphorous: float = Field(
        ..., ge=0.0, le=500.0, validation_alias=AliasChoices("phosphorous", "phosphorus"), description="Phosphorous content"
    )

    @field_validator("nitrogen", "potassium", "phosphorous")
    @classmethod
    def finite_number(cls, value: float) -> float:
        if not math.isfinite(float(value)):
            raise ValueError("must be a finite number")
        return float(value)

    def to_feature_vector(self) -> list[float]:
        # Keep feature order aligned with fertilizer model training columns.
        return [
            float(self.nitrogen),
            float(self.potassium),
            float(self.phosphorous),
        ]

    def to_feature_dict(self) -> dict[str, float]:
        return {
            "nitrogen": float(self.nitrogen),
            "potassium": float(self.potassium),
            "phosphorous": float(self.phosphorous),
        }
