<?php
namespace App\Service;

use App\Entity\Colis;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PredictionService
{
    private string $mlServiceUrl = 'http://localhost:5001';

    public function __construct(private HttpClientInterface $client) {}

    public function predictComplet(Colis $colis): ?array
    {
        try {
            $response = $this->client->request('POST', $this->mlServiceUrl . '/predict-complet', [
                'json' => [
                    'adresse_depart'      => $colis->getAdresseDepart(),
                    'adresse_destination' => $colis->getAdresseDestination(),
                    'poids_kg'            => $colis->getPoids(),
                    'date_debut'          => (new \DateTime())->format('Y-m-d\TH:i:s'),
                ],
                'timeout' => 15,
            ]);

            return $response->toArray();

        } catch (\Exception $e) {
            return null;
        }
    }
}