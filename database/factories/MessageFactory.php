<?php

namespace Database\Factories;

use App\Enums\MessageSender;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'content' => fake('es_MX')->paragraph(),
            'sender' => fake()->randomElement([MessageSender::User, MessageSender::Bot]),
            'type' => MessageType::Text,
            'meta_data' => null,
        ];
    }

    /**
     * Indicate that the message is from the user.
     */
    public function fromUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender' => MessageSender::User,
        ]);
    }

    /**
     * Indicate that the message is from the bot.
     */
    public function fromBot(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender' => MessageSender::Bot,
        ]);
    }

    /**
     * Indicate that the message has quick replies.
     */
    public function withQuickReplies(array $options = []): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MessageType::QuickReplies,
            'meta_data' => [
                'options' => $options ?: [
                    'Opción 1',
                    'Opción 2',
                    'Opción 3',
                ],
            ],
        ]);
    }

    /**
     * Indicate that the message is a card.
     */
    public function card(array $cardData = []): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MessageType::Card,
            'meta_data' => $cardData ?: [
                'title' => fake()->sentence(3),
                'description' => fake()->paragraph(),
            ],
        ]);
    }
}
