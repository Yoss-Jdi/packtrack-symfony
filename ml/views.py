import io
import base64
import json
import pickle
import os
import numpy as np
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

from django.shortcuts import render

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

def prevision_ca(request):

    # 📂 Charger dernières valeurs
    with open(os.path.join(BASE_DIR, 'derniers_ca.json'), 'r') as f:
        data = json.load(f)

    ca_values = data['ca_values']

    # 📂 Charger modèle et scaler
    with open(os.path.join(BASE_DIR, 'model_ca.pkl'), 'rb') as f:
        model = pickle.load(f)

    with open(os.path.join(BASE_DIR, 'scaler_ca.pkl'), 'rb') as f:
        scaler = pickle.load(f)

    # 🔮 Générer 3 prévisions
    last_3 = ca_values.copy()
    predictions = []

    for _ in range(3):
        X_input = np.array([last_3])
        X_scaled = scaler.transform(X_input)
        pred = model.predict(X_scaled)[0]
        predictions.append(round(pred, 2))

        last_3 = [pred] + last_3[:2]

    # 📊 Graphique
    mois_historique = ["M-2", "M-1", "M"]
    mois_prevision = ["M+1", "M+2", "M+3"]

    plt.figure()
    plt.plot(mois_historique, ca_values, label="Historique réel")
    plt.plot(
        ["M"] + mois_prevision,
        [ca_values[-1]] + predictions,
        label="Prévisions IA"
    )

    plt.legend()
    plt.title("Historique réel + Prévisions IA")
    plt.xlabel("Mois")
    plt.ylabel("Montant (DT)")

    buffer = io.BytesIO()
    plt.savefig(buffer, format='png')
    buffer.seek(0)
    image_png = buffer.getvalue()
    buffer.close()

    graphic = base64.b64encode(image_png).decode('utf-8')
    plt.close()

    return render(request, 'facture/prevision_ca.html', {
        'graphic': graphic,
        'predictions': predictions
    })