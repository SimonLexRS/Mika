<?php

namespace App\Services\ChatBrain;

use App\Services\ChatBrain\Context\ConversationContext;

class IntentDetector
{
    /**
     * Patrones de intención con expresiones regulares.
     */
    protected array $patterns = [
        'register_expense' => [
            '/gast[eéoó]/iu',
            '/pagu[eé]/iu',
            '/compr[eéoó]/iu',
            '/registra.*gasto/iu',
            '/anota.*gasto/iu',
            '/agregar.*gasto/iu',
        ],
        'register_income' => [
            '/ingres[eo]/iu',
            '/cobr[eéoó]/iu',
            '/recib[íi]/iu',
            '/me pagaron/iu',
            '/entr[oó].*dinero/iu',
            '/vend[íi]/iu',
            '/registra.*ingreso/iu',
            '/anota.*ingreso/iu',
        ],
        'query_balance' => [
            '/\bsaldo\b/iu',
            '/cu[aá]nto tengo/iu',
            '/\bbalance\b/iu',
            '/c[oó]mo voy/iu',
            '/\bresumen\b/iu',
            '/cu[aá]nto.*queda/iu',
        ],
        'query_transactions' => [
            '/movimientos/iu',
            '/transacciones/iu',
            '/historial/iu',
            '/[uú]ltimos gastos/iu',
            '/qu[eé] he gastado/iu',
            '/mis gastos/iu',
            '/mis ingresos/iu',
        ],
        'greeting' => [
            '/^hola\b/iu',
            '/^buenos d[íi]as/iu',
            '/^buenas tardes/iu',
            '/^buenas noches/iu',
            '/^hey\b/iu',
            '/^qu[eé] tal/iu',
        ],
        'help' => [
            '/\bayuda\b/iu',
            '/qu[eé] puedes hacer/iu',
            '/c[oó]mo funciona/iu',
            '/\bopciones\b/iu',
            '/qu[eé] sabes hacer/iu',
        ],
        'thanks' => [
            '/\bgracias\b/iu',
            '/\bthank/iu',
        ],
    ];

    /**
     * Detectar la intención del mensaje.
     */
    public function detect(string $message, ConversationContext $context): array
    {
        $normalized = $this->normalize($message);

        // Buscar coincidencia en patrones
        foreach ($this->patterns as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $normalized)) {
                    return [
                        'intent' => $intent,
                        'confidence' => 0.8,
                        'matched_pattern' => $pattern,
                    ];
                }
            }
        }

        // Si no se encuentra ninguna intención
        return [
            'intent' => 'unknown',
            'confidence' => 0.0,
            'matched_pattern' => null,
        ];
    }

    /**
     * Normalizar el mensaje.
     */
    protected function normalize(string $message): string
    {
        // Eliminar espacios extra y mantener el caso original para regex con /i
        return trim(preg_replace('/\s+/', ' ', $message));
    }

    /**
     * Agregar un patrón a una intención.
     */
    public function addPattern(string $intent, string $pattern): void
    {
        if (!isset($this->patterns[$intent])) {
            $this->patterns[$intent] = [];
        }
        $this->patterns[$intent][] = $pattern;
    }

    /**
     * Obtener todas las intenciones disponibles.
     */
    public function getAvailableIntents(): array
    {
        return array_keys($this->patterns);
    }
}
