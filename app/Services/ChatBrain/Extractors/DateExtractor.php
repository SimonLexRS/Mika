<?php

namespace App\Services\ChatBrain\Extractors;

use App\Services\ChatBrain\Contracts\ExtractorInterface;
use Carbon\Carbon;

class DateExtractor implements ExtractorInterface
{
    /**
     * Palabras clave para fechas relativas.
     */
    protected array $relativeKeywords = [
        'hoy' => 0,
        'ayer' => -1,
        'anteayer' => -2,
        'antier' => -2,
    ];

    public function extract(string $message): ?Carbon
    {
        $normalized = mb_strtolower($message);

        // Verificar palabras clave relativas
        foreach ($this->relativeKeywords as $keyword => $daysOffset) {
            if (mb_strpos($normalized, $keyword) !== false) {
                return Carbon::now()->addDays($daysOffset)->startOfDay();
            }
        }

        // Buscar patrones de fecha
        // dd/mm/yyyy o dd-mm-yyyy
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $message, $matches)) {
            try {
                $year = strlen($matches[3]) === 2 ? '20' . $matches[3] : $matches[3];
                return Carbon::createFromDate($year, $matches[2], $matches[1]);
            } catch (\Exception $e) {
                // Fecha inválida
            }
        }

        // Buscar "el lunes", "el martes", etc.
        $days = [
            'lunes' => Carbon::MONDAY,
            'martes' => Carbon::TUESDAY,
            'miércoles' => Carbon::WEDNESDAY,
            'miercoles' => Carbon::WEDNESDAY,
            'jueves' => Carbon::THURSDAY,
            'viernes' => Carbon::FRIDAY,
            'sábado' => Carbon::SATURDAY,
            'sabado' => Carbon::SATURDAY,
            'domingo' => Carbon::SUNDAY,
        ];

        foreach ($days as $dayName => $dayNumber) {
            if (mb_strpos($normalized, $dayName) !== false) {
                // Buscar el día más reciente (pasado)
                $date = Carbon::now();
                while ($date->dayOfWeek !== $dayNumber) {
                    $date->subDay();
                }
                return $date->startOfDay();
            }
        }

        // Si dice "la semana pasada"
        if (mb_strpos($normalized, 'semana pasada') !== false) {
            return Carbon::now()->subWeek()->startOfWeek();
        }

        // Si dice "el mes pasado"
        if (mb_strpos($normalized, 'mes pasado') !== false) {
            return Carbon::now()->subMonth()->startOfMonth();
        }

        return null;
    }

    public function getEntityName(): string
    {
        return 'date';
    }
}
