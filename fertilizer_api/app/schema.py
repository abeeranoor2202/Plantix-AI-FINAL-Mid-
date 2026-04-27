from __future__ import annotations

import math
from typing import Any

from pydantic import AliasChoices, BaseModel, ConfigDict, Field, field_validator


class FertilizerInput(BaseModel):
    model_config = ConfigDict(extra="forbid", populate_by_name=True)

    nitrogen: int = Field(..., ge=0, le=500, description="Nitrogen (non-negative integer)")
    potassium: int = Field(
        ..., ge=0, le=500,
        validation_alias=AliasChoices("potassium", "pottasium"),
        description="Potassium (non-negative integer)",
    )
    phosphorous: int = Field(
        ..., ge=0, le=500,
        validation_alias=AliasChoices("phosphorous", "phosphorus"),
        description="Phosphorous (non-negative integer)",
    )

    @field_validator("nitrogen", "potassium", "phosphorous", mode="before")
    @classmethod
    def must_be_integer(cls, value: Any) -> int:
        if isinstance(value, bool):
            raise ValueError("must be a non-negative integer, not a boolean")
        if isinstance(value, float):
            raise ValueError("must be a non-negative integer (decimal values are not allowed)")
        if not isinstance(value, int):
            raise ValueError("must be a non-negative integer")
        return value

    def to_feature_vector(self) -> list[float]:
        # Order must match training: Nitrogen, Potassium, Phosphorous
        return [float(self.nitrogen), float(self.potassium), float(self.phosphorous)]

    def to_feature_dict(self) -> dict[str, int]:
        return {
            "nitrogen": self.nitrogen,
            "potassium": self.potassium,
            "phosphorous": self.phosphorous,
        }
