import pandas as pd
import pickle
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, r2_score

df = pd.read_csv("dataset/dataset_duree.csv")

features = ["distance_km", "poids_kg", "heure_prise_en_charge", "jour_semaine"]
X = df[features]
y = df["duree_minutes"]

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

print("⏳ Entraînement en cours...")
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

y_pred = model.predict(X_test)

print("📊 Résultats du modèle Durée :")
print(f"   MAE (erreur moyenne) : {mean_absolute_error(y_test, y_pred):.2f} minutes")
print(f"   R²  (précision)      : {r2_score(y_test, y_pred):.4f}")

with open("models/model_duree.pkl", "wb") as f:
    pickle.dump(model, f)

print("\n✅ Modèle sauvegardé : models/model_duree.pkl")