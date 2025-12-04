<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Categorías por Defecto
    |--------------------------------------------------------------------------
    */
    'default_categories' => [
        'expense' => [
            'comida' => 'Comida y restaurantes',
            'transporte' => 'Transporte y gasolina',
            'servicios' => 'Servicios y suscripciones',
            'compras' => 'Compras generales',
            'salud' => 'Salud y médicos',
            'entretenimiento' => 'Entretenimiento',
            'educacion' => 'Educación',
            'otros' => 'Otros gastos',
        ],
        'income' => [
            'ventas' => 'Ventas',
            'servicios' => 'Servicios profesionales',
            'freelance' => 'Trabajo freelance',
            'otros' => 'Otros ingresos',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración del Chat
    |--------------------------------------------------------------------------
    */
    'chat' => [
        'max_messages_per_conversation' => 1000,
        'typing_delay_ms' => 500,
        'welcome_message' => '¡Hola! Soy Mika, tu asistente financiero. Puedo ayudarte a registrar gastos e ingresos, consultar tu saldo y mucho más. ¿En qué te puedo ayudar?',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración LLM (Futuro)
    |--------------------------------------------------------------------------
    */
    'llm' => [
        'enabled' => env('MIKA_LLM_ENABLED', false),
        'provider' => env('MIKA_LLM_PROVIDER', 'openai'),
        'model' => env('MIKA_LLM_MODEL', 'gpt-4'),
        'api_key' => env('MIKA_LLM_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Moneda por Defecto
    |--------------------------------------------------------------------------
    */
    'default_currency' => 'MXN',

    /*
    |--------------------------------------------------------------------------
    | Formatos
    |--------------------------------------------------------------------------
    */
    'formats' => [
        'date' => 'd/m/Y',
        'datetime' => 'd/m/Y H:i',
        'money' => '$%s',
    ],
];
