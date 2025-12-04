<?php

namespace App\Services\ChatBrain\Responses;

use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;

class TextResponse implements ResponseBuilderInterface
{
    public function __construct(
        protected string $content,
        protected ?array $followUp = null
    ) {}

    public function getType(): string
    {
        return 'text';
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMetaData(): ?array
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'content' => $this->content,
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
