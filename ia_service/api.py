from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import numpy as np
import hashlib, json, pickle, re
import torch
import torch.nn as nn
import pdfplumber
import io

app = FastAPI(title="Vérification Factures IA")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# ── Autoencoder ───────────────────────────────────────────────────
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

# ── Charger modèle ────────────────────────────────────────────────
with open('modeles/input_dim.pkl', 'rb') as f:
    input_dim = pickle.load(f)
with open('modeles/scaler.pkl', 'rb') as f:
    scaler = pickle.load(f)

threshold = float(np.load('modeles/threshold.npy'))
model = Autoencoder(input_dim)
model.load_state_dict(torch.load('modeles/autoencoder.pth', weights_only=True))
model.eval()

# ── Extraire données du PDF ───────────────────────────────────────
def extraire_donnees_pdf(contenu_pdf: bytes) -> dict:
    texte = ""
    with pdfplumber.open(io.BytesIO(contenu_pdf)) as pdf:
        for page in pdf.pages:
            texte += page.extract_text() or ""

    print("📄 Texte extrait du PDF:\n", texte)

    montantHT = 0.0
    match = re.search(r'Sous-total HT\s*:\s*([\d]+[.,][\d]{2})', texte, re.IGNORECASE)
    if match:
        montantHT = float(match.group(1).replace(',', '.'))

    tva = 0.0
    match = re.search(r'TVA\s*\(([\d]+)%\)', texte, re.IGNORECASE)
    if match:
        tva = float(match.group(1))

    montantTTC = 0.0
    match = re.search(r'TOTAL TTC\s*:\s*([\d]+[.,][\d]{2})', texte, re.IGNORECASE)
    if match:
        montantTTC = float(match.group(1).replace(',', '.'))

    return {
        "montantHT": montantHT,
        "montantTTC": montantTTC,
        "tva": tva,
        "texte_complet": texte
    }

# ── Modèle Pydantic ───────────────────────────────────────────────
class FactureRequest(BaseModel):
    montantHT: float
    montantTTC: float
    tva: float
    heure: int
    jour_semaine: int

# ── Route /verifier (JSON direct depuis Symfony) ──────────────────
@app.post("/verifier")
def verifier_facture(facture: FactureRequest):

    ecart_calcul = abs(
        facture.montantTTC - (facture.montantHT + facture.montantHT * facture.tva / 100)
    )
    tva_legale = 1 if facture.tva in [7, 13, 19, 20] else 0

    hash_facture = hashlib.sha256(
        json.dumps(facture.dict(), sort_keys=True).encode()
    ).hexdigest()

    features = np.array([[
        facture.montantHT, facture.montantTTC, facture.tva,
        facture.heure, facture.jour_semaine, ecart_calcul, tva_legale
    ]])

    features_scaled = scaler.transform(features)
    tensor = torch.FloatTensor(features_scaled)

    with torch.no_grad():
        reconstruction = model(tensor)
        error = float(torch.mean((tensor - reconstruction) ** 2).item())

    est_fraude = error > threshold or ecart_calcul > 0.01 or tva_legale == 0

    return {
        "hash": hash_facture,
        "statut": "SUSPECTE" if est_fraude else "AUTHENTIQUE",
        "score_anomalie": round(error, 6),
        "seuil": round(threshold, 6),
        "details": {
            "coherence_mathematique": ecart_calcul < 0.01,
            "tva_legale": bool(tva_legale),
            "ia_score_normal": error <= threshold
        }
    }

# ── Route /verifier-pdf (upload PDF) ─────────────────────────────
@app.post("/verifier-pdf")
async def verifier_pdf(file: UploadFile = File(...)):

    contenu = await file.read()
    donnees = extraire_donnees_pdf(contenu)
    montantHT  = donnees['montantHT']
    montantTTC = donnees['montantTTC']
    tva        = donnees['tva']

    hash_pdf = hashlib.sha256(contenu).hexdigest()

    ecart_calcul = abs(montantTTC - (montantHT + montantHT * tva / 100))
    tva_legale   = 1 if tva in [7, 13, 19, 20] else 0

    features = np.array([[
        montantHT, montantTTC, tva,
        10, 2,
        ecart_calcul, tva_legale
    ]])

    features_scaled = scaler.transform(features)
    tensor = torch.FloatTensor(features_scaled)

    with torch.no_grad():
        reconstruction = model(tensor)
        error = float(torch.mean((tensor - reconstruction) ** 2).item())

    est_fraude = error > threshold or ecart_calcul > 0.01 or tva_legale == 0

    return {
        "fichier": file.filename,
        "hash_pdf": hash_pdf,
        "donnees_extraites": {
            "montantHT": montantHT,
            "montantTTC": montantTTC,
            "tva": tva
        },
        "statut": "SUSPECTE" if est_fraude else "AUTHENTIQUE",
        "score_anomalie": round(error, 6),
        "seuil": round(threshold, 6),
        "details": {
            "coherence_mathematique": ecart_calcul < 0.01,
            "tva_legale": bool(tva_legale),
            "ia_score_normal": error <= threshold
        }
    }


@app.post("/lire-pdf")
async def lire_pdf(file: UploadFile = File(...)):
    contenu = await file.read()
    texte = ""
    with pdfplumber.open(io.BytesIO(contenu)) as pdf:
        for page in pdf.pages:
            texte += page.extract_text() or ""
    return {"texte": texte}


@app.get("/")
def root():
    return {"message": "✅ API opérationnelle"}