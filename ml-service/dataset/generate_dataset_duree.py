# generate_dataset_duree.py — VERSION AMÉLIORÉE
import pandas as pd
import numpy as np

np.random.seed(42)
n = 5000

# Génère des distances variées incluant des courts trajets urbains
distances_courtes  = np.round(np.random.uniform(1, 15, n // 3), 2)
distances_moyennes = np.round(np.random.uniform(15, 80, n // 3), 2)
distances_longues  = np.round(np.random.uniform(80, 500, n - 2 * (n // 3)), 2)  # ✅ prend le reste exact
distances = np.concatenate([distances_courtes, distances_moyennes, distances_longues])
np.random.shuffle(distances)

data = {
    "distance_km":           distances,
    "poids_kg":              np.round(np.random.uniform(0.5, 30, n), 2),
    "heure_prise_en_charge": np.random.randint(7, 20, n),
    "jour_semaine":          np.random.randint(0, 7, n),
}

df = pd.DataFrame(data)

def calculer_duree(row):
    d   = row["distance_km"]
    h   = row["heure_prise_en_charge"]
    j   = row["jour_semaine"]
    p   = row["poids_kg"]

    # Vitesse de base selon type de trajet
    if d < 15:
        vitesse = 25   # urbain dense Tunis (feux, embouteillages)
    elif d < 80:
        vitesse = 55   # péri-urbain / routes nationales
    else:
        vitesse = 90   # autoroute A1/A3

    duree = (d / vitesse) * 60  # en minutes

    # Manutention/arrêts selon poids
    duree += p * 0.3

    # ⚠️ Heures de pointe Tunis (très marquées)
    if h in [7, 8, 9]:        # matin
        duree *= 1.5
    elif h in [12, 13]:       # pause déjeuner
        duree *= 1.2
    elif h in [17, 18, 19]:   # soir
        duree *= 1.6

    # Vendredi (congestion + prière)
    if j == 4:
        if h in [11, 12, 13]:
            duree *= 1.5
        else:
            duree *= 1.3

    # Weekend tunisien (vendredi-samedi)
    if j == 5:  # samedi
        duree *= 0.85
    if j == 6:  # dimanche (jour travaillé en Tunisie)
        duree *= 0.95

    # Bruit réaliste
    bruit = np.random.normal(0, duree * 0.08)  # ±8% de la durée
    duree += bruit

    return max(5, round(duree, 2))

df["duree_minutes"] = df.apply(calculer_duree, axis=1)

df.to_csv("dataset/dataset_duree.csv", index=False)
print(df.head(10))
print(f"\n✅ Dataset durée généré : {n} lignes")
print(f"   Durée min     : {df['duree_minutes'].min():.1f} min")
print(f"   Durée max     : {df['duree_minutes'].max():.1f} min")
print(f"   Durée moyenne : {df['duree_minutes'].mean():.1f} min")
print(f"\n📊 Exemples de cohérence :")
exemple = df[df["distance_km"] < 10].head(3)
print(exemple[["distance_km", "heure_prise_en_charge", "jour_semaine", "duree_minutes"]])