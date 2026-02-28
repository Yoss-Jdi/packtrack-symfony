from flask import Flask, request, jsonify
from datetime import datetime
import requests as req
import pickle
import numpy as np

app = Flask(__name__)

ORS_KEY = "eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjhlMDdlYzM3ZjY0YjRhMjhiNmRmMjVkMjM5MmRhMDFhIiwiaCI6Im11cm11cjY0In0="

# Charger les modèles
with open("models/model_distance.pkl", "rb") as f:
    model_distance = pickle.load(f)

with open("models/model_duree.pkl", "rb") as f:
    model_duree = pickle.load(f)

def geocode_via_ors(adresse: str):
    """ORS uniquement pour convertir adresse → GPS"""
    try:
        url = "https://api.openrouteservice.org/geocode/search"
        params = {
            "api_key": ORS_KEY,
            "text": adresse + ", Tunisie",
            "size": 1,
            "boundary.country": "TN",
            # ✅ Focus sur la région de Tunis pour éviter les confusions géographiques
            "focus.point.lon": 10.18,
            "focus.point.lat": 36.81,
        }
        r    = req.get(url, params=params, timeout=10)
        data = r.json()
        coords = data["features"][0]["geometry"]["coordinates"]
        return coords[1], coords[0]  # lat, lon
    except Exception as e:
        print(f"❌ Erreur géocodage '{adresse}': {e}")
        return None, None

@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "service": "PackTrack ML Service"})

@app.route("/predict-complet", methods=["POST"])
def predict_complet():
    try:
        data = request.get_json()
        print("📥 Données reçues :", data)

        adresse_depart      = data.get("adresse_depart")
        adresse_destination = data.get("adresse_destination")
        poids_kg            = data.get("poids_kg")
        date_debut          = data.get("date_debut")

        if not all([adresse_depart, adresse_destination, poids_kg, date_debut]):
            return jsonify({"error": "Champs manquants"}), 400

        # 1. ORS → coordonnées GPS (2 appels seulement, pas de Directions)
        lat1, lon1 = geocode_via_ors(adresse_depart)
        lat2, lon2 = geocode_via_ors(adresse_destination)

        if not all([lat1, lon1, lat2, lon2]):
            return jsonify({"error": "Adresse introuvable"}), 400

        print(f"📍 Départ    : lat={lat1}, lon={lon1}")
        print(f"📍 Arrivée   : lat={lat2}, lon={lon2}")

        # 2. Modèle ML prédit la distance réelle
        distance_km = round(float(model_distance.predict(
            np.array([[lat1, lon1, lat2, lon2]])
        )[0]), 2)

        # ✅ Sécurité : distance minimum 0.5 km
        distance_km = max(0.5, distance_km)

        # 3. Modèle ML prédit la durée
        dt           = datetime.fromisoformat(date_debut)
        heure        = dt.hour
        jour_semaine = dt.weekday()

        duree_minutes = round(float(model_duree.predict(
            # ✅ Ordre cohérent avec le training : distance, poids, heure, jour
            np.array([[distance_km, poids_kg, heure, jour_semaine]])
        )[0]), 2)

        # ✅ Sécurité : durée minimum 5 minutes
        duree_minutes = max(5, duree_minutes)

        # 4. Formatter la durée
        heures  = int(duree_minutes // 60)
        minutes = int(duree_minutes % 60)
        duree_formatee = f"{heures}h {minutes}min" if heures > 0 else f"{minutes} min"

        print(f"✅ Distance prédite : {distance_km} km")
        print(f"✅ Durée prédite    : {duree_formatee}")

        return jsonify({
            "distance_km":        distance_km,
            "duree_minutes":      duree_minutes,
            "duree_formatee":     duree_formatee,
            "coords_depart":      {"lat": lat1, "lon": lon1},
            "coords_destination": {"lat": lat2, "lon": lon2},
        })

    except Exception as e:
        print(f"❌ Erreur : {e}")
        return jsonify({"error": str(e)}), 400

if __name__ == "__main__":
    app.run(debug=True, port=5001)