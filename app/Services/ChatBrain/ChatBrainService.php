<?php

namespace App\Services\ChatBrain;

use App\Enums\MessageSender;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;
use App\Services\ChatBrain\Contracts\ExtractorInterface;
use App\Services\ChatBrain\Contracts\IntentHandlerInterface;
use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;
use App\Services\ChatBrain\Intents\HelpIntent;
use Illuminate\Support\Collection;

class ChatBrainService
{
    protected IntentDetector $intentDetector;
    protected Collection $handlers;
    protected array $extractors;

    public function __construct(
        IntentDetector $intentDetector,
        array $handlers = [],
        array $extractors = []
    ) {
        $this->intentDetector = $intentDetector;
        $this->handlers = collect($handlers);
        $this->extractors = $extractors;
    }

    /**
     * Procesar un mensaje del usuario.
     */
    public function process(User $user, string $message): array
    {
        // 1. Obtener o crear conversación activa
        $conversation = $this->getOrCreateConversation($user);

        // 2. Guardar mensaje del usuario
        $userMessage = $conversation->addMessage(
            $message,
            MessageSender::User,
            MessageType::Text
        );

        // 3. Cargar contexto
        $context = new ConversationContext($conversation);

        // 4. Procesar mensaje
        try {
            if ($context->hasPendingFlow()) {
                $response = $this->continuePendingFlow($user, $message, $context);
            } else {
                // Detectar intención
                $intentResult = $this->intentDetector->detect($message, $context);

                // Extraer entidades
                $entities = $this->extractEntities($message);

                // Procesar con handler apropiado
                $response = $this->handleIntent(
                    $user,
                    $message,
                    $intentResult['intent'],
                    $entities,
                    $context
                );
            }
        } catch (\Exception $e) {
            report($e);
            $response = new Responses\TextResponse(
                "Lo siento, ocurrió un error procesando tu mensaje. ¿Podrías intentarlo de nuevo?"
            );
        }

        // 5. Guardar respuesta del bot
        $botMessage = $conversation->addMessage(
            $response->getContent(),
            MessageSender::Bot,
            MessageType::from($response->getType()),
            $response->getMetaData()
        );

        // 6. Actualizar contexto si es necesario
        if ($response->requiresFollowUp()) {
            $context->setPendingFlow($response->getFollowUpContext());
        }

        return [
            'user_message' => $userMessage,
            'bot_response' => $botMessage,
            'response_data' => $response->toArray(),
        ];
    }

    /**
     * Obtener o crear conversación activa.
     */
    protected function getOrCreateConversation(User $user): Conversation
    {
        return $user->getOrCreateActiveConversation();
    }

    /**
     * Extraer todas las entidades del mensaje.
     */
    protected function extractEntities(string $message): array
    {
        $entities = [];

        foreach ($this->extractors as $name => $extractor) {
            if ($extractor instanceof ExtractorInterface) {
                $extracted = $extractor->extract($message);
                if ($extracted !== null) {
                    $entities[$extractor->getEntityName()] = $extracted;
                }
            }
        }

        return $entities;
    }

    /**
     * Manejar una intención.
     */
    protected function handleIntent(
        User $user,
        string $message,
        string $intent,
        array $entities,
        ConversationContext $context
    ): ResponseBuilderInterface {
        // Buscar handler que pueda manejar la intención
        $handler = $this->handlers->first(
            fn(IntentHandlerInterface $h) => $h->canHandle($intent)
        );

        if (!$handler) {
            $handler = $this->getDefaultHandler();
        }

        // Verificar entidades requeridas
        $missing = $this->getMissingEntities($handler, $entities);

        if (!empty($missing)) {
            $context->setPartialData($intent, $entities);
            $context->setExpectingEntity($missing[0]);
            return $handler->askForMissingEntity($missing[0], $context);
        }

        return $handler->handle($user, $message, $entities, $context);
    }

    /**
     * Continuar un flujo pendiente.
     */
    protected function continuePendingFlow(
        User $user,
        string $message,
        ConversationContext $context
    ): ResponseBuilderInterface {
        $flowData = $context->getPendingFlow();
        $intent = $flowData['intent'];
        $existingEntities = $flowData['entities'] ?? [];
        $expecting = $flowData['expecting'] ?? null;

        // Extraer nuevas entidades del mensaje
        $newEntities = $this->extractEntities($message);

        // Si estamos esperando una entidad específica y no se extrajo,
        // intentar interpretar el mensaje como esa entidad
        if ($expecting && !isset($newEntities[$expecting])) {
            $interpreted = $this->interpretAsEntity($message, $expecting);
            if ($interpreted !== null) {
                $newEntities[$expecting] = $interpreted;
            }
        }

        // Combinar entidades existentes con nuevas
        $entities = array_merge($existingEntities, $newEntities);

        // Limpiar flujo pendiente antes de procesar
        $context->clearPendingFlow();

        return $this->handleIntent($user, $message, $intent, $entities, $context);
    }

    /**
     * Obtener entidades faltantes.
     */
    protected function getMissingEntities(IntentHandlerInterface $handler, array $entities): array
    {
        $required = $handler->requiredEntities();
        return array_values(array_diff($required, array_keys($entities)));
    }

    /**
     * Interpretar un mensaje como una entidad específica.
     */
    protected function interpretAsEntity(string $message, string $entityType): mixed
    {
        if (isset($this->extractors[$entityType])) {
            return $this->extractors[$entityType]->extract($message);
        }

        // Para categorías, intentar match directo
        if ($entityType === 'category') {
            $normalized = mb_strtolower(trim($message));
            $categories = ['comida', 'transporte', 'servicios', 'compras', 'salud', 'entretenimiento', 'educacion', 'hogar', 'ventas', 'freelance', 'otros'];
            if (in_array($normalized, $categories)) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * Obtener el handler por defecto.
     */
    protected function getDefaultHandler(): IntentHandlerInterface
    {
        return app(HelpIntent::class);
    }

    /**
     * Agregar un handler.
     */
    public function addHandler(IntentHandlerInterface $handler): void
    {
        $this->handlers->push($handler);
    }

    /**
     * Agregar un extractor.
     */
    public function addExtractor(string $name, ExtractorInterface $extractor): void
    {
        $this->extractors[$name] = $extractor;
    }
}
