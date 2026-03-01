import pandas as pd
import numpy as np
from sqlalchemy import create_engine

engine = create_engine("mysql+pymysql://root:0000@127.0.0.1:3306/trackpackdb")

# Générer 200 factures réalistes
np.random.seed(42)
n = 200

montantHT = np.random.uniform(50, 5000, n).round(2)
tva_pct = np.random.choice([7, 13, 19, 20], n)      # TVA légale en %
montantTVA = (montantHT * tva_pct / 100).round(2)
montantTTC = (montantHT + montantTVA).round(2)
heures = np.random.randint(8, 18, n)                 # heures de travail
jours = np.random.randint(1, 6, n)                   # lundi-vendredi

df = pd.DataFrame({
    'montantHT': montantHT,
    'montantTTC': montantTTC,
    'tva': tva_pct,
    'heure': heures,
    'jour_semaine': jours,
    'ecart_calcul': np.zeros(n),                     # cohérentes
    'tva_legale': np.ones(n)                         # TVA légale
})

print(df.head())
print(f"✅ {len(df)} factures générées")

# Sauvegarder en CSV pour entraîner
df.to_csv('data/factures_train.csv', index=False)
print("✅ Sauvegardé dans data/factures_train.csv")