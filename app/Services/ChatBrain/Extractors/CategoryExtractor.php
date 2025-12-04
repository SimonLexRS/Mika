<?php

namespace App\Services\ChatBrain\Extractors;

use App\Services\ChatBrain\Contracts\ExtractorInterface;

class CategoryExtractor implements ExtractorInterface
{
    /**
     * Palabras clave para detectar categorías.
     */
    protected array $categoryKeywords = [
        'comida' => [
            'comida', 'restaurante', 'almuerzo', 'cena', 'desayuno',
            'comí', 'comer', 'tacos', 'pizza', 'hamburguesa', 'café',
            'lonche', 'antojo', 'botana', 'snack'
        ],
        'transporte' => [
            'uber', 'didi', 'taxi', 'gasolina', 'gas', 'estacionamiento',
            'transporte', 'metro', 'camión', 'pasaje', 'peaje', 'caseta'
        ],
        'servicios' => [
            'luz', 'agua', 'internet', 'teléfono', 'celular', 'netflix',
            'spotify', 'suscripción', 'cable', 'gas natural', 'predial'
        ],
        'compras' => [
            'compra', 'tienda', 'amazon', 'mercado libre', 'ropa',
            'zapatos', 'electrodoméstico', 'mueble', 'decoración'
        ],
        'salud' => [
            'doctor', 'medicina', 'farmacia', 'hospital', 'médico',
            'consulta', 'análisis', 'dentista', 'oftalmólogo', 'receta'
        ],
        'entretenimiento' => [
            'cine', 'concierto', 'fiesta', 'bar', 'diversión',
            'juego', 'videojuego', 'streaming', 'evento', 'boletos'
        ],
        'educacion' => [
            'curso', 'libro', 'escuela', 'universidad', 'capacitación',
            'colegiatura', 'material', 'útiles', 'certificación'
        ],
        'hogar' => [
            'renta', 'alquiler', 'mantenimiento', 'reparación',
            'limpieza', 'lavandería', 'jardinería'
        ],
        'ventas' => [
            'venta', 'vendí', 'cliente', 'factura', 'proyecto',
            'contrato', 'servicio prestado'
        ],
    ];

    public function extract(string $message): ?string
    {
        $normalized = mb_strtolower($message);

        foreach ($this->categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($normalized, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return null;
    }

    public function getEntityName(): string
    {
        return 'category';
    }

    /**
     * Obtener todas las categorías disponibles.
     */
    public function getAvailableCategories(): array
    {
        return array_keys($this->categoryKeywords);
    }

    /**
     * Obtener las palabras clave de una categoría.
     */
    public function getKeywordsFor(string $category): array
    {
        return $this->categoryKeywords[$category] ?? [];
    }
}
