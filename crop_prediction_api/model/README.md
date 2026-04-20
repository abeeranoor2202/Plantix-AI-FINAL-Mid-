Place the trained crop prediction artifact here if you want to keep the model inside this service folder.

Default runtime behavior uses `./RandomForest.pkl` through `MODEL_PATH=./model/RandomForest.pkl`.

If multiple artifacts exist in this folder, ensure `MODEL_PATH` points to the crop recommendation model used by the `/predict` endpoint.

Supported formats:
- `.joblib`
- `.pkl` / `.pickle`
- `.pth` / `.pt` when the file contains a loadable Torch module
