<?php

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';
    case Card = 'card';
    case Image = 'image';
    case QuickReplies = 'quick_replies';
    case Form = 'form';

    public function label(): string
    {
        return match($this) {
            self::Text => 'Texto',
            self::Card => 'Tarjeta',
            self::Image => 'Imagen',
            self::QuickReplies => 'Respuestas rápidas',
            self::Form => 'Formulario',
        };
    }
}
