# generate_dataset_distance.py — VERSION AMÉLIORÉE
import pandas as pd
import numpy as np

np.random.seed(42)

# ✅ Lieux tunisiens RÉELS et précis (quartiers + villes)
lieux = {
    # Grand Tunis - quartiers
    "Tunis Centre":         (36.8065, 10.1815),
    "Ariana":               (36.8625, 10.1956),
    "La Marsa":             (36.8778, 10.3247),
    "Carthage":             (36.8528, 10.3247),
    "Sidi Bou Said":        (36.8683, 10.3411),
    "Le Bardo":             (36.8092, 10.1492),
    "Manouba":              (36.8097, 10.1008),
    "Ben Arous":            (36.7533, 10.2283),
    "Mégrine":              (36.7647, 10.2361),
    "Hammam Lif":           (36.7297, 10.3317),
    "Ezzahra":              (36.7458, 10.2917),
    "Radès":                (36.7681, 10.2742),
    "La Goulette":          (36.8183, 10.3050),
    "Hammam Chott":         (36.7167, 10.3000),
    "Borj Cedria":          (36.7000, 10.3833),
    "Lac 1":                (36.8333, 10.2333),
    "Lac 2":                (36.8500, 10.2500),
    "Ennasr":               (36.8783, 10.2100),
    "Ettadhamen":           (36.8361, 10.1544),
    "Mnihla":               (36.8600, 10.1700),
    "Raoued":               (36.8917, 10.1833),
    "Kalâat el-Andalous":   (37.0000, 10.0833),
    "Zaghouan":             (36.4028, 10.1433),
    "Grombalia":            (36.6000, 10.5000),
    "Nabeul":               (36.4561, 10.7376),
    "Hammamet":             (36.4000, 10.6167),
    "Sousse":               (35.8245, 10.6346),
    "Monastir":             (35.7643, 10.8113),
    "Sfax":                 (34.7406, 10.7603),
    "Kairouan":             (35.6781, 10.0963),
    "Bizerte":              (37.2744, 9.8739),
    "Gabès":                (33.8881, 10.0975),
    "Gafsa":                (34.4250, 8.7842),
    "Médenine":             (33.3547, 10.5053),
    "Djerba":               (33.8075, 10.8451),
}

noms  = list(lieux.keys())
coord = list(lieux.values())

def haversine(lat1, lon1, lat2, lon2):
    R = 6371
    dlat = np.radians(lat2 - lat1)
    dlon = np.radians(lon2 - lon1)
    a = np.sin(dlat/2)**2 + np.cos(np.radians(lat1)) * np.cos(np.radians(lat2)) * np.sin(dlon/2)**2
    return R * 2 * np.arcsin(np.sqrt(a))

def facteur_route(distance_vol_oiseau_km):
    """
    Facteur réaliste selon le type de trajet :
    - Trajet urbain court  (<15 km)  : routes sinueuses → ×1.5 à ×2.2
    - Trajet péri-urbain   (15-50km) : mixte           → ×1.3 à ×1.6
    - Trajet inter-villes  (>50km)   : autoroute       → ×1.1 à ×1.3
    """
    if distance_vol_oiseau_km < 15:
        return np.random.uniform(1.5, 2.2)
    elif distance_vol_oiseau_km < 50:
        return np.random.uniform(1.3, 1.6)
    else:
        return np.random.uniform(1.1, 1.3)

rows = []
n = 5000  # plus de données = meilleur modèle

for _ in range(n):
    i, j = np.random.choice(len(noms), 2, replace=False)
    lat1, lon1 = coord[i]
    lat2, lon2 = coord[j]

    # Légère variation pour simuler des adresses différentes dans le même quartier
    lat1 += np.random.uniform(-0.005, 0.005)
    lon1 += np.random.uniform(-0.005, 0.005)
    lat2 += np.random.uniform(-0.005, 0.005)
    lon2 += np.random.uniform(-0.005, 0.005)

    dist_vol  = round(haversine(lat1, lon1, lat2, lon2), 2)
    facteur   = facteur_route(dist_vol)
    dist_reel = round(dist_vol * facteur, 2)

    rows.append({
        "lieu_depart":            noms[i],
        "lieu_destination":       noms[j],
        "lat_depart":             round(lat1, 6),
        "lon_depart":             round(lon1, 6),
        "lat_destination":        round(lat2, 6),
        "lon_destination":        round(lon2, 6),
        "distance_vol_oiseau_km": dist_vol,
        "distance_reelle_km":     dist_reel,
    })

df = pd.DataFrame(rows)
df.to_csv("dataset/dataset_distances.csv", index=False)

print(df.head(10))
print(f"\n✅ Dataset distances généré : {n} lignes")
print(f"   Distance min : {df['distance_reelle_km'].min()} km")
print(f"   Distance max : {df['distance_reelle_km'].max()} km")
print(f"   Distance moyenne : {df['distance_reelle_km'].mean():.1f} km")
print(f"\n📊 Répartition des trajets :")
print(f"   Courts  (<15km)  : {(df['distance_vol_oiseau_km'] < 15).sum()}")
print(f"   Moyens  (15-50)  : {((df['distance_vol_oiseau_km'] >= 15) & (df['distance_vol_oiseau_km'] < 50)).sum()}")
print(f"   Longs   (>50km)  : {(df['distance_vol_oiseau_km'] >= 50).sum()}")