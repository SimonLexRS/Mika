<?php

namespace App\Services\ChatBrain\Responses;

use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;

class CardResponse implements ResponseBuilderInterface
{
    public function __construct(
        protected string $content,
        protected array $cardData,
        protected ?array $followUp = null
    ) {}

    public function getType(): string
    {
        return 'card';
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMetaData(): ?array
    {
        return $this->cardData;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'content' => $this->content,
            'card' => $this->cardData,
        ];
    }

    public function requiresFollowUp(): bool
    {
        return $this->followUp !== null;
    }

    public function getFollowUpContext(): ?array
    {
        return $this->followUp;
    }
}
