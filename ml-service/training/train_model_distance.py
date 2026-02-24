import pandas as pd
import pickle
import sys
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, r2_score

# Charger le dataset
df = pd.read_csv("dataset/dataset_distances.csv")

features = ["lat_depart", "lon_depart", "lat_destination", "lon_destination"]
X = df[features]
y = df["distance_reelle_km"]

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

print("⏳ Entraînement en cours...")
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

y_pred = model.predict(X_test)

print("📊 Résultats du modèle Distance :")
print(f"   MAE (erreur moyenne) : {mean_absolute_error(y_test, y_pred):.2f} km")
print(f"   R²  (précision)      : {r2_score(y_test, y_pred):.4f}")

# Sauvegarder dans models/
with open("models/model_distance.pkl", "wb") as f:
    pickle.dump(model, f)

print("\n✅ Modèle sauvegardé : models/model_distance.pkl")