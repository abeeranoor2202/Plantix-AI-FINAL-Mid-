"""
plant_filter.py
---------------
Pre-check filter using MobileNetV2 (pretrained on ImageNet via torchvision).
Runs BEFORE the disease model to reject non-plant images (chairs, people, etc.)

No training required — uses ImageNet weights out of the box.
PyTorch/torchvision are already in requirements.txt so no new dependencies needed.
"""

import io
import torch
import torch.nn.functional as F
from torchvision import models, transforms
from PIL import Image

# ── ImageNet label groups that indicate a plant image ──────────────────────────
# These are substrings matched against ImageNet class names returned by MobileNetV2.
PLANT_KEYWORDS = [
    "leaf", "plant", "tree", "flower", "herb", "shrub", "vine",
    "fern", "moss", "fungus", "mushroom", "corn", "wheat", "rice",
    "potato", "tomato", "pepper", "cucumber", "squash", "cabbage",
    "lettuce", "spinach", "broccoli", "cauliflower", "artichoke",
    "banana", "mango", "orange", "lemon", "strawberry", "raspberry",
    "grape", "apple", "pear", "peach", "cherry", "fig", "pineapple",
    "daisy", "sunflower", "rose", "tulip", "orchid", "dandelion",
    "acorn", "conifer", "palm", "bamboo", "cactus", "aloe",
    "bud", "petal", "stalk", "stem", "root", "seed", "pod",
    "crop", "field", "garden", "soil", "hay", "straw",
]

# Confidence threshold: MobileNet must score at least this for a plant class
PLANT_CONFIDENCE_THRESHOLD = 0.10  # 10% — intentionally low to avoid false rejections

# ── Load MobileNetV2 once at module import (cached for the lifetime of the process) ──
_mobilenet = None
_mobilenet_labels = None

# ImageNet class labels (1000 classes) — loaded lazily
def _get_imagenet_labels():
    """Return the list of 1000 ImageNet class name strings."""
    global _mobilenet_labels
    if _mobilenet_labels is not None:
        return _mobilenet_labels

    # torchvision bundles the ImageNet class index → label mapping
    from torchvision.models import MobileNet_V2_Weights
    weights = MobileNet_V2_Weights.IMAGENET1K_V1
    _mobilenet_labels = [meta["category"] for meta in weights.meta["categories"]]
    return _mobilenet_labels


def _get_mobilenet():
    """Load and cache MobileNetV2 with pretrained ImageNet weights."""
    global _mobilenet
    if _mobilenet is not None:
        return _mobilenet

    from torchvision.models import MobileNet_V2_Weights
    weights = MobileNet_V2_Weights.IMAGENET1K_V1
    model = models.mobilenet_v2(weights=weights)
    model.eval()
    _mobilenet = model
    return _mobilenet


# Preprocessing pipeline matching MobileNetV2 ImageNet training
_preprocess = transforms.Compose([
    transforms.Resize(256),
    transforms.CenterCrop(224),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485, 0.456, 0.406],
                         std=[0.229, 0.224, 0.225]),
])


def is_plant_image(image_bytes: bytes) -> tuple[bool, str, float]:
    """
    Determine whether the uploaded image contains a plant / leaf.

    Parameters
    ----------
    image_bytes : bytes
        Raw image bytes (JPEG / PNG / WebP).

    Returns
    -------
    is_plant : bool
        True if the image looks like a plant.
    top_label : str
        The highest-confidence ImageNet label detected.
    top_confidence : float
        Confidence score (0–1) for that label.
    """
    try:
        image = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    except Exception:
        return False, "unreadable", 0.0

    tensor = _preprocess(image).unsqueeze(0)  # shape: (1, 3, 224, 224)

    with torch.no_grad():
        logits = _get_mobilenet()(tensor)
        probs  = F.softmax(logits, dim=1)[0]  # shape: (1000,)

    labels = _get_imagenet_labels()

    # Top-5 predictions
    top5_probs, top5_indices = torch.topk(probs, 5)

    top_label      = labels[top5_indices[0].item()]
    top_confidence = top5_probs[0].item()

    # Check if any of the top-5 predictions match a plant keyword
    for idx, prob in zip(top5_indices.tolist(), top5_probs.tolist()):
        label_lower = labels[idx].lower()
        if any(kw in label_lower for kw in PLANT_KEYWORDS):
            if prob >= PLANT_CONFIDENCE_THRESHOLD:
                return True, labels[idx], prob

    return False, top_label, top_confidence
