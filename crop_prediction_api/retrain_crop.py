"""
Retrain the crop recommendation RandomForest model using the current
scikit-learn version and overwrite RandomForest.pkl so it is compatible
with the running environment.

Usage:
    .\\env\\Scripts\\python.exe retrain_crop.py
"""
import pathlib
import joblib
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score
import sklearn

BASE_DIR  = pathlib.Path(__file__).resolve().parent
CSV_PATH  = BASE_DIR.parent / "AIModules" / "Data-processed" / "crop_recommendation.csv"
PKL_PATH  = BASE_DIR / "model" / "RandomForest.pkl"

# ── Load data ────────────────────────────────────────────────────────────────
df = pd.read_csv(CSV_PATH)
print(f"Loaded {len(df)} rows  |  columns: {df.columns.tolist()}")

FEATURES = ["N", "P", "K", "temperature", "humidity", "ph", "rainfall"]
X = df[FEATURES].values
y = df["label"].values

# ── Train ────────────────────────────────────────────────────────────────────
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

model = RandomForestClassifier(n_estimators=100, random_state=42, n_jobs=-1)
model.fit(X_train, y_train)

acc = accuracy_score(y_test, model.predict(X_test))
print(f"Test accuracy : {acc:.4f}")

# ── Save ─────────────────────────────────────────────────────────────────────
joblib.dump(model, PKL_PATH)
print(f"Model saved   : {PKL_PATH}")
print(f"sklearn ver   : {sklearn.__version__}")
