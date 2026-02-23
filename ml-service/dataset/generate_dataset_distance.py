# generate_dataset_distance.py
import pandas as pd
import numpy as np

np.random.seed(42)
n = 1000

# Villes tunisiennes avec leurs coordonnées GPS réelles
villes = {
    "Tunis":    (36.8065, 10.1815),
    "Sfax":     (34.7406, 10.7603),
    "Sousse":   (35.8245, 10.6346),
    "Monastir": (35.7643, 10.8113),
    "Nabeul":   (36.4561, 10.7376),
    "Bizerte":  (37.2744, 9.8739),
    "Gabès":    (33.8881, 10.0975),
    "Kairouan": (35.6781, 10.0963),
    "Ariana":   (36.8625, 10.1956),
    "Gafsa":    (34.4250, 8.7842),
}

noms_villes = list(villes.keys())
coords      = list(villes.values())

def haversine(lat1, lon1, lat2, lon2):
    R = 6371  # rayon terre en km
    dlat = np.radians(lat2 - lat1)
    dlon = np.radians(lon2 - lon1)
    a = np.sin(dlat/2)**2 + np.cos(np.radians(lat1)) * np.cos(np.radians(lat2)) * np.sin(dlon/2)**2
    return R * 2 * np.arcsin(np.sqrt(a))

rows = []
for _ in range(n):
    i, j = np.random.choice(len(noms_villes), 2, replace=False)
    lat1, lon1 = coords[i]
    lat2, lon2 = coords[j]
    distance   = round(haversine(lat1, lon1, lat2, lon2), 2)
    # Bruit réaliste : la route réelle est toujours plus longue qu'à vol d'oiseau
    distance_route = round(distance * np.random.uniform(1.2, 1.5), 2)

    rows.append({
        "ville_depart":      noms_villes[i],
        "ville_destination": noms_villes[j],
        "lat_depart":        lat1,
        "lon_depart":        lon1,
        "lat_destination":   lat2,
        "lon_destination":   lon2,
        "distance_vol_oiseau_km": distance,
        "distance_reelle_km":     distance_route,  # ← cible
    })

df = pd.DataFrame(rows)
df.to_csv("dataset_distances.csv", index=False)
print(df.head(10))
print(f"\n✅ Dataset distances généré : {n} lignes")