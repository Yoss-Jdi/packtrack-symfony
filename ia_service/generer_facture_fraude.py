from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas

def generer_facture_fraudee():
    c = canvas.Canvas("data/facture_fraudee.pdf", pagesize=A4)
    width, height = A4

    c.setFont("Helvetica-Bold", 20)
    c.drawString(220, 800, "FACTURE")

    c.setFont("Helvetica", 12)
    c.drawString(50, 770, "FAC-999")
    c.drawString(50, 750, "TrackPack")
    c.drawString(50, 730, "Service de livraison professionnel")
    c.drawString(50, 710, "N° Facture : FAC-999     Date d'émission : 01/03/2026")

    c.drawString(50, 670, "Informations Client")
    c.drawString(50, 650, "Nom : Client Suspect")
    c.drawString(50, 630, "Email : suspect@mail.com")

    c.drawString(50, 590, "Description          Quantité    Montant")
    c.drawString(50, 570, "Service de livraison - Colis #99")
    c.drawString(50, 550, "1                   99999.00 DT")   # ← montant bizarre

    # ⚠️ Fraude : montantHT * TVA ≠ montantTTC
    c.drawString(50, 510, "Sous-total HT : 99999.00 DT")       # ← montant gonflé
    c.drawString(50, 490, "TVA (20%) : 100.00 DT")             # ← TVA incorrecte
    c.drawString(50, 470, "TOTAL TTC : 99999.00 DT")           # ← TTC ≠ HT + TVA

    c.drawString(50, 430, "TrackPack - Service de livraison professionnel")
    c.save()
    print("✅ Facture fraudée générée : data/facture_fraudee.pdf")

generer_facture_fraudee()