FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app

COPY crop_prediction_api/requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir -r /app/requirements.txt

COPY crop_prediction_api /app
COPY crop_prediction_api/model /app/model

ENV FLASK_ENV=production \
    APP_ENV=production \
    MODEL_PATH=/app/model/RandomForest.pkl \
    DATABASE_PATH=/app/instance/predictions.sqlite3 \
    PORT=8000

EXPOSE 8000

CMD ["gunicorn", "--bind", "0.0.0.0:8000", "wsgi:app", "--workers", "2", "--timeout", "120"]
