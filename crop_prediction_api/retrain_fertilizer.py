"""
Retrain the fertilizer recommendation model using the current sklearn version
and overwrite fertilizer.pkl so it is compatible with the running environment.
"""
import pathlib
import joblib
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score

BASE_DIR = pathlib.Path(__file__).resolve().parent
CSV_PATH  = BASE_DIR / "model" / "Fertilizer.csv"
PKL_PATH  = BASE_DIR / "model" / "fertilizer.pkl"

# ── Load data ────────────────────────────────────────────────────────────────
df = pd.read_csv(CSV_PATH)
print(f"Loaded {len(df)} rows from {CSV_PATH}")
print("Columns:", df.columns.tolist())

# Column names in the CSV: Nitrogen, Potassium, Phosphorous, Fertilizer Name
X = df[["Nitrogen", "Potassium", "Phosphorous"]].values
y = df["Fertilizer Name"].values

# ── Train ────────────────────────────────────────────────────────────────────
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

acc = accuracy_score(y_test, model.predict(X_test))
print(f"Test accuracy: {acc:.4f}")

# ── Save ─────────────────────────────────────────────────────────────────────
joblib.dump(model, PKL_PATH)
print(f"Model saved to {PKL_PATH}")

import sklearn
print(f"sklearn version used: {sklearn.__version__}")
