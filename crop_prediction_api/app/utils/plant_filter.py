"""
plant_filter.py
===============
MobileNetV2 pre-check filter — runs BEFORE the VGG16 disease model.

Uses torchvision's MobileNetV2 pretrained on ImageNet (1000 real-world classes)
to decide whether an image contains a plant/leaf. If it doesn't, we hard-stop
and never call the disease model.

Why MobileNet instead of confidence threshold on VGG16:
  - VGG16 is trained ONLY on plant leaves → it has no concept of "chair" or
    "person". It will always pick the closest leaf class with high confidence
    even for completely unrelated images.
  - MobileNet knows 1000 real-world ImageNet classes, so it can confidently
    identify chairs, people, cars, etc. and reject them.

No new dependencies: torch + torchvision are already in requirements.txt.
Model weights are downloaded once (~14 MB) and cached by torchvision.
"""

from __future__ import annotations

import io
import logging

import torch
import torch.nn.functional as F
from PIL import Image
from torchvision import models, transforms
from torchvision.models import MobileNet_V2_Weights

logger = logging.getLogger(__name__)

# ---------------------------------------------------------------------------
# Plant-related ImageNet label keywords
# Intentionally broad — we want to ACCEPT borderline cases (fail open).
# Only clear non-plant images (chairs, people, cars, etc.) are rejected.
# ---------------------------------------------------------------------------
PLANT_KEYWORDS: frozenset[str] = frozenset([
    # Direct plant parts
    "leaf", "plant", "tree", "flower", "herb", "shrub", "vine",
    "fern", "moss", "fungus", "mushroom", "bud", "petal", "stalk",
    "stem", "root", "seed", "pod", "frond", "spore",
    # Crops & fruits
    "corn", "wheat", "rice", "potato", "tomato", "pepper", "cucumber",
    "squash", "cabbage", "lettuce", "spinach", "broccoli", "cauliflower",
    "artichoke", "banana", "mango", "orange", "lemon", "strawberry",
    "raspberry", "grape", "apple", "pear", "peach", "cherry", "fig",
    "pineapple", "soybean", "cotton",
    # Flowers & ornamentals
    "daisy", "sunflower", "rose", "tulip", "orchid", "dandelion",
    "hibiscus", "lotus", "lily", "cactus", "aloe",
    # Trees & woody plants
    "acorn", "conifer", "palm", "bamboo", "oak", "maple", "pine",
    "willow", "birch", "cedar", "spruce",
    # Agricultural context
    "crop", "field", "garden", "hay", "straw", "soil",
])

# Minimum confidence for a plant keyword match to count as "plant"
# Kept low (10%) to avoid false rejections on unusual plant photos
PLANT_MIN_CONFIDENCE: float = 0.10

# ---------------------------------------------------------------------------
# Load MobileNetV2 once at module import — cached for the process lifetime
# ---------------------------------------------------------------------------
_weights = MobileNet_V2_Weights.IMAGENET1K_V1
_mobilenet: torch.nn.Module = models.mobilenet_v2(weights=_weights)
_mobilenet.eval()

# ImageNet class labels (1000 entries)
_IMAGENET_LABELS: list[str] = [
    meta["category"] for meta in _weights.meta["categories"]
]

# Preprocessing pipeline matching MobileNetV2 ImageNet training
_preprocess = transforms.Compose([
    transforms.Resize(256),
    transforms.CenterCrop(224),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406],
                         std=[0.229, 0.224, 0.225]),
])


def check_is_plant(image_bytes: bytes) -> tuple[bool, str, float]:
    """
    Determine whether the uploaded image contains a plant or leaf.

    Parameters
    ----------
    image_bytes : bytes
        Raw image bytes (JPEG / PNG / WebP).

    Returns
    -------
    is_plant : bool
        True  → image looks like a plant — proceed to disease model.
        False → image is NOT a plant — hard stop, return "invalid".
    top_label : str
        The highest-confidence ImageNet label detected (for error messages).
    top_confidence : float
        Confidence (0–1) for that label.

    Algorithm
    ---------
    1. Run MobileNetV2 on the image.
    2. Get top-5 ImageNet predictions.
    3. If ANY of the top-5 predictions match a plant keyword AND has
       confidence >= PLANT_MIN_CONFIDENCE → accept as plant.
    4. Otherwise → reject.
    """
    try:
        image = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    except Exception as exc:
        logger.warning("plant_filter: could not open image: %s", exc)
        # Fail open — let the disease model handle it
        return True, "unreadable", 0.0

    tensor = _preprocess(image).unsqueeze(0)  # (1, 3, 224, 224)

    with torch.no_grad():
        logits = _mobilenet(tensor)
        probs  = F.softmax(logits, dim=1)[0]  # (1000,)

    top5_probs, top5_indices = torch.topk(probs, 5)

    top_label      = _IMAGENET_LABELS[top5_indices[0].item()]
    top_confidence = top5_probs[0].item()

    # Debug: log all top-5 predictions so you can inspect them
    top5_decoded = [
        (
            _IMAGENET_LABELS[idx.item()],
            round(prob.item(), 4),
        )
        for idx, prob in zip(top5_indices, top5_probs)
    ]
    logger.info("MobileNet top-5: %s", top5_decoded)

    # Check each top-5 prediction for plant keywords
    for idx, prob in zip(top5_indices.tolist(), top5_probs.tolist()):
        label_lower = _IMAGENET_LABELS[idx].lower()
        if prob >= PLANT_MIN_CONFIDENCE:
            for keyword in PLANT_KEYWORDS:
                if keyword in label_lower:
                    logger.info(
                        "plant_filter: ACCEPTED — matched keyword '%s' in '%s' (%.1f%%)",
                        keyword, _IMAGENET_LABELS[idx], prob * 100,
                    )
                    return True, _IMAGENET_LABELS[idx], prob

    logger.info(
        "plant_filter: REJECTED — top label='%s' (%.1f%%), no plant keywords matched",
        top_label, top_confidence * 100,
    )
    return False, top_label, top_confidence
