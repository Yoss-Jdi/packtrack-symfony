<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class VehicleProblemAnalyzer
{
    private HttpClientInterface $httpClient;
    private string $provider;
    private string $huggingfaceKey;
    private string $groqKey;
    private string $ollamaUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        string $aiProvider = 'fallback',
        string $huggingfaceKey = '',
        string $groqKey = '',
        string $ollamaUrl = 'http://localhost:11434'
    ) {
        $this->httpClient = $httpClient;
        $this->provider = $aiProvider;
        $this->huggingfaceKey = $huggingfaceKey;
        $this->groqKey = $groqKey;
        $this->ollamaUrl = $ollamaUrl;
    }

    public function analyzeProblem(string $description, string $marque, string $modele, string $type): array
    {
        $prompt = $this->buildPrompt($description, $marque, $modele, $type);

        try {
            switch ($this->provider) {
                case 'huggingface':
                    return $this->analyzeWithHuggingFace($prompt, $description);
                case 'groq':
                    return $this->analyzeWithGroq($prompt, $description);
                case 'ollama':
                    return $this->analyzeWithOllama($prompt, $description);
                default:
                    return $this->getFallbackAnalysis($description);
            }
        } catch (\Exception $e) {
            return $this->getFallbackAnalysis($description);
        }
    }

    private function buildPrompt(string $description, string $marque, string $modele, string $type): string
    {
        return sprintf(
            "Tu es un expert en maintenance de véhicules. Analyse ce problème et fournis une réponse structurée.\n\n" .
            "Véhicule: %s %s (Type: %s)\n" .
            "Problème: %s\n\n" .
            "Fournis une analyse avec:\n" .
            "1. Diagnostic probable (2-3 phrases)\n" .
            "2. Actions recommandées (liste de 3-5 points)\n" .
            "3. Niveau d'urgence (Faible/Moyen/Élevé)\n" .
            "4. Temps estimé (en heures)",
            $marque,
            $modele,
            $type,
            $description
        );
    }

    private function analyzeWithHuggingFace(string $prompt, string $originalDescription): array
    {
        if (empty($this->huggingfaceKey)) {
            return $this->getFallbackAnalysis($originalDescription);
        }

        $response = $this->httpClient->request('POST', 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.2', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->huggingfaceKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'inputs' => $prompt,
                'parameters' => [
                    'max_new_tokens' => 500,
                    'temperature' => 0.7,
                    'return_full_text' => false,
                ],
            ],
            'timeout' => 30,
        ]);

        $data = $response->toArray();
        $aiResponse = $data[0]['generated_text'] ?? '';

        return $this->parseAiResponse($aiResponse, $originalDescription);
    }

    private function analyzeWithGroq(string $prompt, string $originalDescription): array
    {
        if (empty($this->groqKey)) {
            return $this->getFallbackAnalysis($originalDescription);
        }

        $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->groqKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'llama3-8b-8192',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un expert en maintenance de véhicules. Analyse les problèmes et fournis des recommandations claires.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ],
            'timeout' => 30,
        ]);

        $data = $response->toArray();
        $aiResponse = $data['choices'][0]['message']['content'] ?? '';

        return $this->parseAiResponse($aiResponse, $originalDescription);
    }

    private function analyzeWithOllama(string $prompt, string $originalDescription): array
    {
        $response = $this->httpClient->request('POST', $this->ollamaUrl . '/api/generate', [
            'json' => [
                'model' => 'llama2',
                'prompt' => $prompt,
                'stream' => false,
            ],
            'timeout' => 60,
        ]);

        $data = $response->toArray();
        $aiResponse = $data['response'] ?? '';

        return $this->parseAiResponse($aiResponse, $originalDescription);
    }

    private function parseAiResponse(string $response, string $originalDescription): array
    {
        return [
            'diagnosis' => $this->extractSection($response, 'Diagnostic') ?: 'Problème nécessitant une inspection technique.',
            'actions' => $this->extractActions($response),
            'urgency' => $this->extractUrgency($response),
            'estimatedTime' => $this->extractTime($response),
            'fullAnalysis' => $response,
            'originalDescription' => $originalDescription,
        ];
    }

    private function extractSection(string $text, string $section): ?string
    {
        $patterns = [
            "/(?:$section|Diagnostic)(?:\s*probable)?[\s:]+(.+?)(?=\n\n|\n[0-9]|\nActions|$)/si",
            "/(?:$section)[\s:]+(.+?)(?=\n\n|$)/si",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function extractActions(string $text): array
    {
        $actions = [];
        
        if (preg_match('/Actions?\s*recommandées?[\s:]+(.+?)(?=\n\n|Niveau|Temps|$)/si', $text, $matches)) {
            $actionText = $matches[1];
            preg_match_all('/[-•*]\s*(.+?)(?=\n|$)/i', $actionText, $actionMatches);
            
            if (!empty($actionMatches[1])) {
                $actions = array_map('trim', $actionMatches[1]);
            }
        }

        if (empty($actions)) {
            preg_match_all('/^\d+\.\s*(.+?)$/m', $text, $numberedMatches);
            if (!empty($numberedMatches[1])) {
                $actions = array_slice(array_map('trim', $numberedMatches[1]), 0, 5);
            }
        }

        return $actions ?: ['Inspecter le véhicule', 'Diagnostiquer le problème', 'Effectuer les réparations nécessaires'];
    }

    private function extractUrgency(string $text): string
    {
        if (preg_match('/(?:Niveau|Urgence)[\s:]+(\w+)/i', $text, $matches)) {
            $urgency = strtolower($matches[1]);
            if (strpos($urgency, 'lev') !== false || strpos($urgency, 'high') !== false) {
                return 'Élevé';
            }
            if (strpos($urgency, 'moyen') !== false || strpos($urgency, 'medium') !== false) {
                return 'Moyen';
            }
            return 'Faible';
        }

        $lowerText = strtolower($text);
        if (strpos($lowerText, 'urgent') !== false || strpos($lowerText, 'immédiat') !== false) {
            return 'Élevé';
        }

        return 'Moyen';
    }

    private function extractTime(string $text): string
    {
        if (preg_match('/(?:Temps|Durée)[\s:]+(\d+(?:[.,]\d+)?)\s*(?:heures?|h)/i', $text, $matches)) {
            return $matches[1] . ' heures';
        }

        if (preg_match('/(\d+)\s*(?:heures?|h)/i', $text, $matches)) {
            return $matches[1] . ' heures';
        }

        return '2-4 heures';
    }

    private function getFallbackAnalysis(string $description): array
    {
        $lowerDesc = strtolower($description);
        
        $urgency = 'Moyen';
        $estimatedTime = '2-4 heures';
        $diagnosis = 'Problème nécessitant une inspection technique détaillée.';
        $actions = [
            'Inspecter visuellement le véhicule',
            'Effectuer un diagnostic complet',
            'Identifier les pièces défectueuses',
            'Procéder aux réparations nécessaires',
            'Tester le véhicule après réparation',
        ];

        if (strpos($lowerDesc, 'moteur') !== false || strpos($lowerDesc, 'démarr') !== false) {
            $diagnosis = 'Problème moteur détecté. Nécessite une inspection du système de démarrage et du moteur.';
            $actions = [
                'Vérifier la batterie et les connexions',
                'Inspecter le système de démarrage',
                'Contrôler le système d\'allumage',
                'Vérifier le système d\'injection',
                'Tester le démarrage après réparation',
            ];
            $urgency = 'Élevé';
            $estimatedTime = '3-6 heures';
        } elseif (strpos($lowerDesc, 'frein') !== false) {
            $diagnosis = 'Problème de freinage identifié. Intervention urgente requise pour la sécurité.';
            $actions = [
                'Inspecter les plaquettes et disques de frein',
                'Vérifier le niveau de liquide de frein',
                'Contrôler le système hydraulique',
                'Remplacer les pièces usées',
                'Effectuer un test de freinage',
            ];
            $urgency = 'Élevé';
            $estimatedTime = '2-3 heures';
        } elseif (strpos($lowerDesc, 'pneu') !== false || strpos($lowerDesc, 'roue') !== false) {
            $diagnosis = 'Problème de pneumatiques ou de roues détecté.';
            $actions = [
                'Inspecter l\'état des pneus',
                'Vérifier la pression des pneus',
                'Contrôler l\'alignement des roues',
                'Remplacer les pneus si nécessaire',
                'Effectuer un équilibrage',
            ];
            $estimatedTime = '1-2 heures';
        } elseif (strpos($lowerDesc, 'huile') !== false || strpos($lowerDesc, 'fuite') !== false) {
            $diagnosis = 'Fuite ou problème de lubrification détecté.';
            $actions = [
                'Localiser la source de la fuite',
                'Vérifier le niveau d\'huile',
                'Inspecter les joints et les durites',
                'Réparer ou remplacer les pièces défectueuses',
                'Faire l\'appoint d\'huile si nécessaire',
            ];
            $urgency = 'Moyen';
            $estimatedTime = '2-4 heures';
        }

        return [
            'diagnosis' => $diagnosis,
            'actions' => $actions,
            'urgency' => $urgency,
            'estimatedTime' => $estimatedTime,
            'fullAnalysis' => sprintf(
                "Diagnostic: %s\n\nActions recommandées:\n%s\n\nNiveau d'urgence: %s\nTemps estimé: %s",
                $diagnosis,
                implode("\n", array_map(fn($a, $i) => ($i + 1) . '. ' . $a, $actions, array_keys($actions))),
                $urgency,
                $estimatedTime
            ),
            'originalDescription' => $description,
        ];
    }
}
