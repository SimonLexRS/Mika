<?php

namespace App\Services\ChatBrain\Extractors;

use App\Services\ChatBrain\Contracts\ExtractorInterface;

class AmountExtractor implements ExtractorInterface
{
    /**
     * Patrones para extraer montos.
     */
    protected array $patterns = [
        // $1,234.56 o $1234.56 o 1,234.56 o 1234.56
        '/\$?\s*(\d{1,3}(?:,\d{3})*(?:\.\d{1,2})?)/u',
        // 100 pesos, 50 dlls, etc.
        '/(\d+(?:\.\d{1,2})?)\s*(?:pesos?|mxn|dlls?|usd|dólares?)/iu',
        // 5 mil, 5k
        '/(\d+(?:\.\d{1,2})?)\s*(?:mil|k)\b/iu',
    ];

    public function extract(string $message): ?float
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return $this->parseAmount($matches[1], $message);
            }
        }

        return null;
    }

    /**
     * Parsear el monto extraído.
     */
    protected function parseAmount(string $value, string $originalMessage): float
    {
        // Remover comas y símbolos
        $clean = str_replace([',', '$'], '', $value);

        // Verificar si el mensaje contiene "mil" o "k"
        if (preg_match('/(\d+(?:\.\d{1,2})?)\s*(?:mil|k)\b/iu', $originalMessage, $matches)) {
            return floatval($matches[1]) * 1000;
        }

        return floatval($clean);
    }

    public function getEntityName(): string
    {
        return 'amount';
    }
}
