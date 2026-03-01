import json
import pickle
import numpy as np
import os

base_dir = os.path.dirname(os.path.abspath(__file__))

with open(os.path.join(base_dir, 'model_ca.pkl'), 'rb') as f:
    model = pickle.load(f)

with open(os.path.join(base_dir, 'scaler_ca.pkl'), 'rb') as f:
    scaler = pickle.load(f)

# ✅ Lire les 3 derniers CA réels
with open(os.path.join(base_dir, 'derniers_ca.json'), 'r') as f:
    derniers = json.load(f)

ca_recents = derniers['ca_values']  # [CA_mois-3, CA_mois-2, CA_mois-1]

# ✅ Lire les mois futurs depuis input.json
with open(os.path.join(base_dir, 'input.json'), 'r') as f:
    data = json.load(f)

mois_noms = [
    'Janvier', 'Février', 'Mars', 'Avril',
    'Mai', 'Juin', 'Juillet', 'Août',
    'Septembre', 'Octobre', 'Novembre', 'Décembre'
]

predictions = []
ca_glissant = list(ca_recents)  # copie des 3 derniers CA

for i, mois_data in enumerate(data['mois_futurs']):

    # ✅ Fenêtre glissante : utilise les 3 derniers CA connus
    X = np.array([[
        ca_glissant[-1],  # CA mois -1
        ca_glissant[-2],  # CA mois -2
        ca_glissant[-3],  # CA mois -3
    ]])

    X_scaled = scaler.transform(X)
    ca_prevu  = model.predict(X_scaled)[0]
    ca_prevu  = round(max(ca_prevu, 0), 2)

    predictions.append({
        'mois':     mois_noms[(mois_data['mois'] - 1) % 12],
        'ca_prevu': ca_prevu,
        'tendance': 'hausse' if ca_prevu > ca_glissant[-1] else 'baisse',
        'base_sur': f"Basé sur: {ca_glissant[-3]:.0f}, {ca_glissant[-2]:.0f}, {ca_glissant[-1]:.0f} DT"
    })

    # ✅ Ajouter la prédiction comme nouveau point glissant
    ca_glissant.append(ca_prevu)

print(json.dumps({'predictions': predictions}))