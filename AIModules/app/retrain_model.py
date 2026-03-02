"""
Script to retrain the crop recommendation model with the current scikit-learn version
and save it as RandomForest.pkl
"""
import pandas as pd
import pickle
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder

# Load dataset
import os
BASE = os.path.dirname(os.path.abspath(__file__))
data = pd.read_csv(os.path.join(BASE, '..', 'Data-processed', 'crop_recommendation.csv'))

X = data.drop('label', axis=1)
y = data['label']

# Train/test split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train Random Forest
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

accuracy = model.score(X_test, y_test)
print(f"Model accuracy: {accuracy:.4f}")

# Save model
model_path = os.path.join(BASE, 'models', 'RandomForest.pkl')
with open(model_path, 'wb') as f:
    pickle.dump(model, f)

print(f"Model saved to {model_path}")
