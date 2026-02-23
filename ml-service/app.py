from flask import Flask, request, jsonify
from geopy.geocoders import Nominatim
from geopy.exc import GeocoderTimedOut
from datetime import datetime
import pickle
import numpy as np

app = Flask(__name__)

# Charger les deux modèles
with open("models/model_distance.pkl", "rb") as f:
    model_distance = pickle.load(f)

with open("models/model_duree.pkl", "rb") as f:
    model_duree = pickle.load(f)

geolocator = Nominatim(user_agent="colis_app_pidev")

def geocode_adresse(adresse: str):
    try:
        location = geolocator.geocode(adresse + ", Tunisie", timeout=10)
        if location:
            return location.latitude, location.longitude
        return None, None
    except GeocoderTimedOut:
        return None, None

# ─────────────────────────────────────────
@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "message": "ML Service opérationnel"})

# ─────────────────────────────────────────
@app.route("/predict-distance", methods=["POST"])
def predict_distance():
    try:
        data = request.get_json()

        adresse_depart      = data.get("adresse_depart")
        adresse_destination = data.get("adresse_destination")

        if not adresse_depart or not adresse_destination:
            return jsonify({"error": "adresse_depart et adresse_destination sont obligatoires"}), 400

        print(f"📍 Géocodage de : {adresse_depart}")
        lat1, lon1 = geocode_adresse(adresse_depart)

        print(f"📍 Géocodage de : {adresse_destination}")
        lat2, lon2 = geocode_adresse(adresse_destination)

        if not all([lat1, lon1, lat2, lon2]):
            return jsonify({"error": "Impossible de géocoder une des adresses"}), 400

        distance_predite = round(float(model_distance.predict(
            np.array([[lat1, lon1, lat2, lon2]])
        )[0]), 2)

        print(f"✅ Distance prédite : {distance_predite} km")

        return jsonify({
            "distance_km":        distance_predite,
            "coords_depart":      {"lat": lat1, "lon": lon1},
            "coords_destination": {"lat": lat2, "lon": lon2},
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 400

# ─────────────────────────────────────────
@app.route("/predict-duree", methods=["POST"])
def predict_duree():
    try:
        data = request.get_json()

        distance_km = data.get("distance_km")
        poids_kg    = data.get("poids_kg")
        date_debut  = data.get("date_debut")  # format : "2025-01-15T08:30:00"

        if not all([distance_km, poids_kg, date_debut]):
            return jsonify({"error": "distance_km, poids_kg et date_debut sont obligatoires"}), 400

        # Extraire heure et jour depuis date_debut
        dt          = datetime.fromisoformat(date_debut)
        heure       = dt.hour
        jour_semaine = dt.weekday()  # 0=lundi, 6=dimanche

        duree_predite = round(float(model_duree.predict(
            np.array([[distance_km, poids_kg, heure, jour_semaine]])
        )[0]), 2)

        # Formatter en heures/minutes
        heures  = int(duree_predite // 60)
        minutes = int(duree_predite % 60)

        if heures > 0:
            duree_formatee = f"{heures}h {minutes}min"
        else:
            duree_formatee = f"{minutes} min"

        print(f"✅ Durée prédite : {duree_formatee}")

        return jsonify({
            "duree_minutes":  duree_predite,
            "duree_formatee": duree_formatee,
            "heure_utilisee": heure,
            "jour_utilise":   jour_semaine,
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 400

# ─────────────────────────────────────────
@app.route("/predict-complet", methods=["POST"])
def predict_complet():
    """
    Endpoint tout-en-un : adresses → distance prédite → durée prédite
    C'est cet endpoint que Symfony va appeler principalement
    """
    try:
        data = request.get_json()

        adresse_depart      = data.get("adresse_depart")
        adresse_destination = data.get("adresse_destination")
        poids_kg            = data.get("poids_kg")
        date_debut          = data.get("date_debut")

        if not all([adresse_depart, adresse_destination, poids_kg, date_debut]):
            return jsonify({"error": "adresse_depart, adresse_destination, poids_kg et date_debut sont obligatoires"}), 400

        # 1. Géocoder
        lat1, lon1 = geocode_adresse(adresse_depart)
        lat2, lon2 = geocode_adresse(adresse_destination)

        if not all([lat1, lon1, lat2, lon2]):
            return jsonify({"error": "Impossible de géocoder une des adresses"}), 400

        # 2. Prédire la distance
        distance_km = round(float(model_distance.predict(
            np.array([[lat1, lon1, lat2, lon2]])
        )[0]), 2)

        # 3. Prédire la durée
        dt           = datetime.fromisoformat(date_debut)
        heure        = dt.hour
        jour_semaine = dt.weekday()

        duree_minutes = round(float(model_duree.predict(
            np.array([[distance_km, poids_kg, heure, jour_semaine]])
        )[0]), 2)

        heures  = int(duree_minutes // 60)
        minutes = int(duree_minutes % 60)
        duree_formatee = f"{heures}h {minutes}min" if heures > 0 else f"{minutes} min"

        print(f"✅ Distance : {distance_km} km | Durée : {duree_formatee}")

        return jsonify({
            "distance_km":        distance_km,
            "duree_minutes":      duree_minutes,
            "duree_formatee":     duree_formatee,
            "coords_depart":      {"lat": lat1, "lon": lon1},
            "coords_destination": {"lat": lat2, "lon": lon2},
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 400

if __name__ == "__main__":
    app.run(debug=True, port=5001)