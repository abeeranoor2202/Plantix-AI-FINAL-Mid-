from __future__ import annotations

import json
import sqlite3
from pathlib import Path
from threading import Lock

from ..errors import DatabaseError
from ..models.prediction import PredictionRecord


class PredictionRepository:
    def __init__(self, database_path: str | Path):
        self.database_path = Path(database_path)
        self._lock = Lock()
        self.database_path.parent.mkdir(parents=True, exist_ok=True)

    def _connect(self):
        connection = sqlite3.connect(self.database_path, check_same_thread=False)
        connection.row_factory = sqlite3.Row
        return connection

    def init_db(self) -> None:
        try:
            with self._connect() as connection:
                connection.execute(
                    """
                    CREATE TABLE IF NOT EXISTS predictions (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        request_id TEXT NOT NULL UNIQUE,
                        prediction TEXT NOT NULL,
                        confidence REAL,
                        features_json TEXT NOT NULL,
                        model_name TEXT NOT NULL,
                        model_version TEXT NOT NULL,
                        client_ip TEXT,
                        user_agent TEXT,
                        created_at TEXT NOT NULL
                    )
                    """
                )
                connection.execute(
                    "CREATE INDEX IF NOT EXISTS idx_predictions_created_at ON predictions(created_at DESC)"
                )
                connection.execute("CREATE INDEX IF NOT EXISTS idx_predictions_model_name ON predictions(model_name)")
        except sqlite3.Error as exc:
            raise DatabaseError(f"Failed to initialize the prediction store: {exc}") from exc

    def healthcheck(self) -> bool:
        try:
            with self._connect() as connection:
                connection.execute("SELECT 1")
            return True
        except sqlite3.Error:
            return False

    def log_prediction(self, record: PredictionRecord) -> int:
        try:
            with self._lock, self._connect() as connection:
                cursor = connection.execute(
                    """
                    INSERT INTO predictions (
                        request_id,
                        prediction,
                        confidence,
                        features_json,
                        model_name,
                        model_version,
                        client_ip,
                        user_agent,
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    """,
                    (
                        record.request_id,
                        record.prediction,
                        record.confidence,
                        record.features_json,
                        record.model_name,
                        record.model_version,
                        record.client_ip,
                        record.user_agent,
                        record.created_at,
                    ),
                )
                connection.commit()
                return int(cursor.lastrowid)
        except sqlite3.Error as exc:
            raise DatabaseError(f"Failed to log prediction: {exc}") from exc

    def list_predictions(self, limit: int = 50, offset: int = 0) -> list[dict]:
        try:
            with self._connect() as connection:
                rows = connection.execute(
                    """
                    SELECT *
                    FROM predictions
                    ORDER BY created_at DESC, id DESC
                    LIMIT ? OFFSET ?
                    """,
                    (limit, offset),
                ).fetchall()
        except sqlite3.Error as exc:
            raise DatabaseError(f"Failed to fetch prediction history: {exc}") from exc

        return [self._row_to_dict(row) for row in rows]

    def get_stats(self) -> dict:
        try:
            with self._connect() as connection:
                total_predictions = connection.execute("SELECT COUNT(*) AS total FROM predictions").fetchone()["total"]
                latest_row = connection.execute(
                    "SELECT created_at FROM predictions ORDER BY created_at DESC, id DESC LIMIT 1"
                ).fetchone()
                top_rows = connection.execute(
                    """
                    SELECT prediction, COUNT(*) AS count
                    FROM predictions
                    GROUP BY prediction
                    ORDER BY count DESC, prediction ASC
                    LIMIT 10
                    """
                ).fetchall()
                average_confidence_row = connection.execute(
                    "SELECT AVG(confidence) AS avg_confidence FROM predictions WHERE confidence IS NOT NULL"
                ).fetchone()
        except sqlite3.Error as exc:
            raise DatabaseError(f"Failed to fetch prediction stats: {exc}") from exc

        return {
            "total_predictions": int(total_predictions or 0),
            "latest_prediction_at": latest_row["created_at"] if latest_row else None,
            "average_confidence": float(average_confidence_row["avg_confidence"]) if average_confidence_row and average_confidence_row["avg_confidence"] is not None else None,
            "top_predictions": [{"prediction": row["prediction"], "count": int(row["count"])} for row in top_rows],
        }

    @staticmethod
    def _row_to_dict(row: sqlite3.Row) -> dict:
        return {
            "id": row["id"],
            "request_id": row["request_id"],
            "prediction": row["prediction"],
            "confidence": row["confidence"],
            "features": json.loads(row["features_json"]),
            "model_name": row["model_name"],
            "model_version": row["model_version"],
            "client_ip": row["client_ip"],
            "user_agent": row["user_agent"],
            "created_at": row["created_at"],
        }
