from sqlalchemy import create_engine
import pandas as pd

# Remplace root et par ton mot de passe si tu en as un
engine = create_engine("mysql+pymysql://root:0000@127.0.0.1:3306/trackpackdb")
def charger_factures():
    query = """
        SELECT 
            montantHT,
            montantTTC,
            tva,
            HOUR(dateEmission) as heure,
            DAYOFWEEK(dateEmission) as jour_semaine,
            ABS(montantTTC - (montantHT + montantHT * tva / 100)) as ecart_calcul,
            CASE WHEN tva IN (7, 13, 19) THEN 1 ELSE 0 END as tva_legale
        FROM factures
        WHERE montantHT IS NOT NULL
    """
    df = pd.read_sql(query, engine)
    return df

if __name__ == "__main__":
    df = charger_factures()
    print(f"✅ {len(df)} factures chargées")
    print(df.head())