<?php

namespace webignition\ValidatorMessage;

abstract class AbstractMessage implements \JsonSerializable
{
    const KEY_TYPE = 'type';
    const KEY_MESSAGE = 'message';

    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    private $type;
    private $message;

    public function __construct(string $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function withMessage(string $message): self
    {
        $new = clone $this;
        $new->message = $message;

        return $new;
    }

    public function isError(): bool
    {
        return self::TYPE_ERROR === $this->type;
    }

    public function isWarning(): bool
    {
        return self::TYPE_WARNING === $this->type;
    }

    public function isInfo(): bool
    {
        return self::TYPE_INFO === $this->type;
    }

    public function jsonSerialize(): array
    {
        return [
            self::KEY_TYPE => $this->type,
            self::KEY_MESSAGE => $this->message,
        ];
    }

    public function getHash()
    {
        return md5((string) json_encode($this));
    }
}
