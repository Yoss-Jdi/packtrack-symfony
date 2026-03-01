import numpy as np
import pandas as pd
import pickle
import torch
import torch.nn as nn
from sklearn.preprocessing import MinMaxScaler

# ── 1. Charger données ────────────────────────────────────────────
df = pd.read_csv('data/factures_train.csv')
print(f"✅ {len(df)} factures chargées")

features = ['montantHT', 'montantTTC', 'tva',
            'heure', 'jour_semaine', 'ecart_calcul', 'tva_legale']

X = df[features].fillna(0).values

# ── 2. Normaliser ─────────────────────────────────────────────────
scaler = MinMaxScaler()
X_scaled = scaler.fit_transform(X)

with open('modeles/scaler.pkl', 'wb') as f:
    pickle.dump(scaler, f)

# ── 3. Autoencoder ────────────────────────────────────────────────
class Autoencoder(nn.Module):
    def __init__(self, input_dim):
        super().__init__()
        self.encoder = nn.Sequential(
            nn.Linear(input_dim, 16), nn.ReLU(),
            nn.Linear(16, 8),         nn.ReLU(),
            nn.Linear(8, 3)
        )
        self.decoder = nn.Sequential(
            nn.Linear(3, 8),          nn.ReLU(),
            nn.Linear(8, 16),         nn.ReLU(),
            nn.Linear(16, input_dim), nn.Sigmoid()
        )
    def forward(self, x):
        return self.decoder(self.encoder(x))

input_dim = X_scaled.shape[1]
model = Autoencoder(input_dim)
optimizer = torch.optim.Adam(model.parameters(), lr=0.001)
criterion = nn.MSELoss()
X_tensor = torch.FloatTensor(X_scaled)

# ── 4. Entraîner ──────────────────────────────────────────────────
print("🚀 Entraînement en cours...")
for epoch in range(100):
    model.train()
    optimizer.zero_grad()
    output = model(X_tensor)
    loss = criterion(output, X_tensor)
    loss.backward()
    optimizer.step()
    if (epoch+1) % 10 == 0:
        print(f"Epoch {epoch+1}/100 - Loss: {loss.item():.6f}")

# ── 5. Calculer seuil ─────────────────────────────────────────────
model.eval()
with torch.no_grad():
    reconstructions = model(X_tensor)
    errors = torch.mean((X_tensor - reconstructions) ** 2, dim=1).numpy()

threshold = float(np.mean(errors) + 2 * np.std(errors))
print(f"\n✅ Seuil : {threshold:.6f}")

# ── 6. Sauvegarder ────────────────────────────────────────────────
torch.save(model.state_dict(), 'modeles/autoencoder.pth')
np.save('modeles/threshold.npy', np.array(threshold))
with open('modeles/input_dim.pkl', 'wb') as f:
    pickle.dump(input_dim, f)

print("✅ Modèle sauvegardé dans modeles/")