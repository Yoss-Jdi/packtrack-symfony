<?php

namespace App\Service;

use App\Entity\Facture;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FraudeDetectionService
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function verifierFacture(Facture $facture): array
    {
        try {
            // Calculer le % TVA depuis le montant stocké en BDD
            // Ex: montantHT=60, tva=12 → 12/60*100 = 20%
            $tvaPourcentage = $facture->getMontantHT() > 0
                ? round(($facture->getTva() / $facture->getMontantHT()) * 100)
                : 20;

            $response = $this->httpClient->request('POST', 'http://127.0.0.1:8002/verifier', [
                'json' => [
                    'montantHT'    => $facture->getMontantHT(),
                    'montantTTC'   => $facture->getMontantTTC(),
                    'tva'          => $tvaPourcentage,
                    'heure'        => (int) $facture->getDateEmission()->format('H'),
                    'jour_semaine' => (int) $facture->getDateEmission()->format('N'),
                ],
                'timeout' => 30,
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            return [
                'statut'  => '❌ ERREUR',
                'message' => 'Service IA indisponible : ' . $e->getMessage()
            ];
        }
    }
}