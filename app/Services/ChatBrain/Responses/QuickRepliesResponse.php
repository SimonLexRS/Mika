<?php

namespace App\Services\ChatBrain\Responses;

use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;

class QuickRepliesResponse implements ResponseBuilderInterface
{
    public function __construct(
        protected string $content,
        protected array $options,
        protected ?array $followUp = null
    ) {}

    public function getType(): string
    {
        return 'quick_replies';
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMetaData(): ?array
    {
        return [
            'options' => $this->options,
        ];
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'content' => $this->content,
            'options' => $this->options,
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
