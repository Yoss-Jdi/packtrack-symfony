<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    private HttpClientInterface $httpClient;
    private string $provider;
    private string $groqKey;

    public function __construct(
        HttpClientInterface $httpClient,
        string $aiProvider = 'fallback',
        string $groqKey = ''
    ) {
        $this->httpClient = $httpClient;
        $this->provider = $aiProvider;
        $this->groqKey = $groqKey;
    }

    public function chat(string $message, array $conversationHistory = []): array
    {
        if ($this->provider === 'groq' && !empty($this->groqKey)) {
            return $this->chatWithGroq($message, $conversationHistory);
        }

        return $this->getFallbackResponse($message);
    }

    private function chatWithGroq(string $message, array $conversationHistory): array
    {
        try {
            // Build messages array with system prompt and conversation history
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Tu es un assistant virtuel pour PackTrack, une plateforme de suivi de colis et de gestion de livraisons. Tu aides les utilisateurs avec leurs questions sur le suivi de colis, les livraisons, le forum communautaire, et les fonctionnalités de la plateforme. Sois amical, professionnel et concis. Réponds toujours en français.'
                ]
            ];

            // Add conversation history (last 10 messages to keep context manageable)
            $recentHistory = array_slice($conversationHistory, -10);
            foreach ($recentHistory as $msg) {
                $messages[] = [
                    'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $msg['content']
                ];
            }

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];

            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray(false); // false = don't throw on error status
            
            // Check if there's an error in the response
            if (isset($data['error'])) {
                throw new \Exception('Groq API Error: ' . ($data['error']['message'] ?? 'Unknown error'));
            }

            $aiResponse = $data['choices'][0]['message']['content'] ?? '';

            return [
                'success' => true,
                'message' => $aiResponse,
                'timestamp' => time(),
            ];
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('Chatbot Groq Error: ' . $e->getMessage());
            return $this->getFallbackResponse($message);
        }
    }

    private function getFallbackResponse(string $message): array
    {
        $lowerMessage = strtolower($message);

        // Keyword-based responses
        if (strpos($lowerMessage, 'bonjour') !== false || strpos($lowerMessage, 'salut') !== false || strpos($lowerMessage, 'hello') !== false) {
            $response = "Bonjour ! 👋 Je suis l'assistant virtuel de PackTrack. Comment puis-je vous aider aujourd'hui ?";
        } elseif (strpos($lowerMessage, 'colis') !== false || strpos($lowerMessage, 'suivi') !== false || strpos($lowerMessage, 'tracking') !== false) {
            $response = "Pour suivre votre colis, vous pouvez utiliser notre système de suivi en temps réel. Avez-vous votre numéro de suivi ?";
        } elseif (strpos($lowerMessage, 'livraison') !== false || strpos($lowerMessage, 'délai') !== false) {
            $response = "Nos délais de livraison varient selon la destination. En général, les livraisons locales prennent 1-2 jours, et les livraisons nationales 3-5 jours ouvrables.";
        } elseif (strpos($lowerMessage, 'forum') !== false || strpos($lowerMessage, 'communauté') !== false || strpos($lowerMessage, 'publication') !== false) {
            $response = "Notre forum communautaire vous permet de partager vos expériences, poser des questions et interagir avec d'autres utilisateurs. Vous pouvez créer des publications, commenter et réagir aux posts.";
        } elseif (strpos($lowerMessage, 'problème') !== false || strpos($lowerMessage, 'aide') !== false || strpos($lowerMessage, 'support') !== false) {
            $response = "Je suis là pour vous aider ! Pouvez-vous me décrire votre problème plus en détail ? Vous pouvez aussi contacter notre support technique si nécessaire.";
        } elseif (strpos($lowerMessage, 'compte') !== false || strpos($lowerMessage, 'inscription') !== false || strpos($lowerMessage, 'connexion') !== false) {
            $response = "Pour créer un compte ou vous connecter, utilisez le bouton de connexion en haut de la page. Si vous avez oublié votre mot de passe, vous pouvez le réinitialiser.";
        } elseif (strpos($lowerMessage, 'prix') !== false || strpos($lowerMessage, 'tarif') !== false || strpos($lowerMessage, 'coût') !== false) {
            $response = "Nos tarifs dépendent du poids, de la taille et de la destination de votre colis. Contactez notre service commercial pour un devis personnalisé.";
        } elseif (strpos($lowerMessage, 'merci') !== false) {
            $response = "De rien ! 😊 N'hésitez pas si vous avez d'autres questions. Je suis là pour vous aider !";
        } elseif (strpos($lowerMessage, 'au revoir') !== false || strpos($lowerMessage, 'bye') !== false) {
            $response = "Au revoir ! 👋 N'hésitez pas à revenir si vous avez besoin d'aide. Bonne journée !";
        } else {
            $response = "Je comprends votre question. Pour une assistance plus détaillée, je vous recommande de :\n\n• Consulter notre FAQ\n• Contacter notre support technique\n• Poser votre question sur le forum communautaire\n\nComment puis-je vous aider autrement ?";
        }

        return [
            'success' => true,
            'message' => $response,
            'timestamp' => time(),
        ];
    }
}
