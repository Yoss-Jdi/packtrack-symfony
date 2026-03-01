import json
import numpy as np
from sklearn.linear_model import LinearRegression
from sklearn.preprocessing import StandardScaler
import pickle
import os

base_dir = os.path.dirname(os.path.abspath(__file__))

# ✅ Lire les vraies données
train_file = os.path.join(base_dir, 'train_data.json')

if os.path.exists(train_file):
    with open(train_file, 'r') as f:
        raw = json.load(f)
    data = [d for d in raw['data'] if d['ca'] > 0]
else:
    data = [
        {'mois': 10, 'ca': 180},
        {'mois': 11, 'ca':  48},
        {'mois': 12, 'ca':  96},
        {'mois':  1, 'ca':  72},
        {'mois':  2, 'ca': 396},
    ]

ca_values = [d['ca'] for d in data]

# ✅ Créer les features glissantes (fenêtre de 3 mois)
X = []
y = []

for i in range(3, len(ca_values)):
    X.append([
        ca_values[i-1],  # CA mois -1
        ca_values[i-2],  # CA mois -2
        ca_values[i-3],  # CA mois -3
    ])
    y.append(ca_values[i])

if len(X) < 2:
    # Pas assez de données → fallback données fictives
    X = [
        [120, 100, 90],
        [150, 120, 100],
        [180, 150, 120],
        [160, 180, 150],
        [200, 160, 180],
        [220, 200, 160],
        [250, 220, 200],
        [230, 250, 220],
        [270, 230, 250],
    ]
    y = [160, 180, 160, 200, 220, 250, 230, 270, 300]

X = np.array(X)
y = np.array(y)

scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

model = LinearRegression()
model.fit(X_scaled, y)

with open(os.path.join(base_dir, 'model_ca.pkl'), 'wb') as f:
    pickle.dump(model, f)

with open(os.path.join(base_dir, 'scaler_ca.pkl'), 'wb') as f:
    pickle.dump(scaler, f)

# ✅ Sauvegarder aussi les dernières valeurs pour predict
derniers = {'ca_values': ca_values[-3:]}
with open(os.path.join(base_dir, 'derniers_ca.json'), 'w') as f:
    json.dump(derniers, f)

score = model.score(X_scaled, y) * 100 if len(X) >= 2 else 0
print(f"Modele glissant entraine sur {len(X)} sequences. R2: {score:.1f}%")
import matplotlib.pyplot as plt

# 🔮 Générer 3 prévisions futures
last_3 = ca_values[-3:]
predictions = []

for _ in range(3):
    X_input = np.array([last_3])
    X_input_scaled = scaler.transform(X_input)
    pred = model.predict(X_input_scaled)[0]
    predictions.append(round(pred, 2))

    # glissement
    last_3 = [pred] + last_3[:2]

# 📊 Mois (exemple simple)
mois_historique = [f"M{i+1}" for i in range(len(ca_values))]
mois_prevision = [f"P{i+1}" for i in range(3)]

plt.figure()

plt.plot(mois_historique, ca_values, label="Historique réel")
plt.plot(
    mois_historique[-1:] + mois_prevision,
    [ca_values[-1]] + predictions,
    label="Prévisions IA"
)

plt.legend()
plt.title("Historique réel + Prévisions IA")
plt.xlabel("Mois")
plt.ylabel("Montant (DT)")

plt.show()