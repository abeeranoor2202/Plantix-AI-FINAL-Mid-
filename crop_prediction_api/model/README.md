Place the trained crop prediction artifact here if you want to keep the model inside this service folder.

Default runtime behavior uses `../models/RandomForest.pkl` so the existing workspace model works without copying.

Supported formats:
- `.joblib`
- `.pkl` / `.pickle`
- `.pth` / `.pt` when the file contains a loadable Torch module
