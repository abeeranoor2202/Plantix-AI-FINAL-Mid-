# Crop Prediction Flask API

Production-ready Flask API for crop recommendation with startup-time model loading, request validation, API-key auth, CORS, rate limiting, and SQLite prediction logging.

## Folder Structure

```text
crop_prediction_api/
  app/
    config.py
    extensions.py
    errors.py
    models/
    routes/
    schemas/
    services/
    utils/
  docs/
  instance/
  model/
  Dockerfile
  requirements.txt
  run.py
  wsgi.py
```

## Environment

Copy `.env.example` to `.env` and set:

- `MODEL_PATH` to your local trained model file
- `API_KEY` to a strong secret
- `SECRET_KEY` to a strong secret
- `ALLOWED_ORIGINS` to your website origin(s)

Default model path points to `./model/RandomForest.pkl` so the bundled model works out of the box.

## Local Run

```bash
cd crop_prediction_api
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
copy .env.example .env
python run.py
```

The API listens on `http://localhost:5000`.

## Endpoints

- `POST /predict`
- `POST /fertilizer/predict`
- `GET /health`
- `GET /model-info`
- `GET /fertilizer/model-info`
- `GET /admin/predictions`
- `GET /admin/stats`

## Authentication

Send the API key with:

```bash
X-API-Key: your-api-key
```

## Example Request

```bash
curl -X POST http://localhost:5000/predict ^
  -H "Content-Type: application/json" ^
  -H "X-API-Key: replace-with-a-long-random-api-key" ^
  -d "{\"nitrogen\":90,\"phosphorus\":42,\"potassium\":43,\"temperature\":22.5,\"humidity\":82,\"ph\":6.5,\"rainfall\":200}"
```

## Example Response

```json
{
  "success": true,
  "prediction": "wheat",
  "confidence": 0.87,
  "request_id": "1e6c1b1a-6d3c-4d40-a2a5-2af58a8c8f1c",
  "record_id": 12,
  "timestamp": "2026-04-19T12:34:56.123456+00:00"
}
```

## Docker

Build from the repository root so the Dockerfile can copy the bundled model:

```bash
docker build -f crop_prediction_api/Dockerfile -t crop-prediction-api .
docker run -p 8000:8000 --env-file crop_prediction_api/.env.example crop-prediction-api
```

## Notes for Frontend/Admin Panel

- `POST /predict` accepts JSON directly from a user-facing form.
- Prediction history is persisted in SQLite for admin dashboards.
- CORS is enabled for configured frontend origins.
- API-key auth is enforced for prediction and admin routes.
