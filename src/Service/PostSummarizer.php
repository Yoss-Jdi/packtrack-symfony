<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostSummarizer
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

    public function summarize(string $title, string $content, int $commentCount = 0): array
    {
        // If content is too short, no need for AI summary
        if (strlen($content) < 200) {
            return [
                'summary' => strip_tags($content),
                'keyPoints' => [],
                'sentiment' => 'neutral',
                'readingTime' => 1,
            ];
        }

        try {
            if ($this->provider === 'groq' && !empty($this->groqKey)) {
                return $this->summarizeWithGroq($title, $content, $commentCount);
            }
        } catch (\Exception $e) {
            // Fallback to simple summary
        }

        return $this->createFallbackSummary($content);
    }

    private function summarizeWithGroq(string $title, string $content, int $commentCount): array
    {
        $cleanContent = strip_tags($content);
        $cleanContent = substr($cleanContent, 0, 3000); // Limit content length

        $prompt = sprintf(
            "Analyse cette publication de forum et fournis un résumé structuré.\n\n" .
            "Titre: %s\n" .
            "Contenu: %s\n" .
            "Nombre de commentaires: %d\n\n" .
            "Fournis:\n" .
            "1. Un résumé concis (2-3 phrases)\n" .
            "2. 3-5 points clés (liste)\n" .
            "3. Le sentiment général (positif/neutre/négatif)\n" .
            "4. Temps de lecture estimé (en minutes)",
            $title,
            $cleanContent,
            $commentCount
        );

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
                        'content' => 'Tu es un assistant qui résume des publications de forum de manière claire et concise. Réponds toujours en français.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.5,
                'max_tokens' => 500,
            ],
            'timeout' => 30,
        ]);

        $data = $response->toArray();
        $aiResponse = $data['choices'][0]['message']['content'] ?? '';

        return $this->parseAiSummary($aiResponse, $cleanContent);
    }

    private function parseAiSummary(string $response, string $originalContent): array
    {
        $summary = '';
        $keyPoints = [];
        $sentiment = 'neutral';
        $readingTime = max(1, (int) ceil(str_word_count($originalContent) / 200));

        // Extract summary
        if (preg_match('/(?:résumé|summary)[\s:]+(.+?)(?=\n\n|points? clés?|sentiment|$)/si', $response, $matches)) {
            $summary = trim($matches[1]);
        } else {
            // Take first paragraph as summary
            $lines = explode("\n", trim($response));
            $summary = trim($lines[0]);
        }

        // Extract key points
        preg_match_all('/[-•*]\s*(.+?)(?=\n|$)/i', $response, $pointMatches);
        if (!empty($pointMatches[1])) {
            $keyPoints = array_slice(array_map('trim', $pointMatches[1]), 0, 5);
        }

        // If no bullet points found, try numbered list
        if (empty($keyPoints)) {
            preg_match_all('/^\d+\.\s*(.+?)$/m', $response, $numberedMatches);
            if (!empty($numberedMatches[1])) {
                $keyPoints = array_slice(array_map('trim', $numberedMatches[1]), 0, 5);
            }
        }

        // Extract sentiment
        if (preg_match('/sentiment[\s:]+(\w+)/i', $response, $sentimentMatch)) {
            $sentimentText = strtolower($sentimentMatch[1]);
            if (strpos($sentimentText, 'positif') !== false || strpos($sentimentText, 'positive') !== false) {
                $sentiment = 'positive';
            } elseif (strpos($sentimentText, 'négatif') !== false || strpos($sentimentText, 'negative') !== false) {
                $sentiment = 'negative';
            }
        }

        // Extract reading time
        if (preg_match('/(\d+)\s*minutes?/i', $response, $timeMatch)) {
            $readingTime = (int) $timeMatch[1];
        }

        return [
            'summary' => $summary ?: substr($originalContent, 0, 200) . '...',
            'keyPoints' => $keyPoints,
            'sentiment' => $sentiment,
            'readingTime' => $readingTime,
            'fullAnalysis' => $response,
        ];
    }

    private function createFallbackSummary(string $content): array
    {
        $cleanContent = strip_tags($content);
        $wordCount = str_word_count($cleanContent);
        $readingTime = max(1, (int) ceil($wordCount / 200));

        // Create simple summary (first 200 characters)
        $summary = substr($cleanContent, 0, 200);
        if (strlen($cleanContent) > 200) {
            $summary .= '...';
        }

        // Extract sentences as key points
        $sentences = preg_split('/[.!?]+/', $cleanContent, -1, PREG_SPLIT_NO_EMPTY);
        $keyPoints = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) > 20 && strlen($sentence) < 150) {
                $keyPoints[] = $sentence;
                if (count($keyPoints) >= 3) {
                    break;
                }
            }
        }

        // Simple sentiment analysis based on keywords
        $sentiment = 'neutral';
        $positiveWords = ['bon', 'bien', 'excellent', 'super', 'génial', 'parfait', 'merci', 'bravo'];
        $negativeWords = ['mauvais', 'problème', 'erreur', 'bug', 'cassé', 'défaut', 'mal'];

        $lowerContent = strtolower($cleanContent);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($lowerContent, $word);
        }
        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($lowerContent, $word);
        }

        if ($positiveCount > $negativeCount && $positiveCount > 0) {
            $sentiment = 'positive';
        } elseif ($negativeCount > $positiveCount && $negativeCount > 0) {
            $sentiment = 'negative';
        }

        return [
            'summary' => $summary,
            'keyPoints' => $keyPoints,
            'sentiment' => $sentiment,
            'readingTime' => $readingTime,
        ];
    }
}
