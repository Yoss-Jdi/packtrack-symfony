import pandas as pd
import numpy as np

np.random.seed(42)
n = 1000

data = {
    "distance_km":           np.round(np.random.uniform(10, 450, n), 2),
    "poids_kg":              np.round(np.random.uniform(0.5, 30, n), 2),
    "heure_prise_en_charge": np.random.randint(7, 20, n),
    "jour_semaine":          np.random.randint(0, 7, n),  # 0=lundi, 6=dimanche
}

df = pd.DataFrame(data)

def calculer_duree(row):
    duree = 15  # base en minutes
    duree += row["distance_km"] * 1.8        # vitesse moyenne ~33km/h
    duree += row["poids_kg"] * 0.5           # manutention
    # Heures de pointe Tunis
    if row["heure_prise_en_charge"] in [8, 9, 17, 18, 19]:
        duree *= 1.4
    # Vendredi = congestion en Tunisie
    if row["jour_semaine"] == 4:
        duree *= 1.3
    # Weekend = moins de trafic
    if row["jour_semaine"] >= 5:
        duree *= 0.85
    # Bruit réaliste
    duree += np.random.normal(0, 10)
    return max(10, round(duree, 2))

df["duree_minutes"] = df.apply(calculer_duree, axis=1)

df.to_csv("dataset/dataset_duree.csv", index=False)
print(df.head(10))
print(f"\n✅ Dataset durée généré : {n} lignes")
print(f"   Durée min : {df['duree_minutes'].min()} min")
print(f"   Durée max : {df['duree_minutes'].max()} min")
print(f"   Durée moyenne : {df['duree_minutes'].mean():.1f} min")