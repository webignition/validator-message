<?php

namespace webignition\ValidatorMessage;

interface MessageInterface
{
    const KEY_TYPE = 'type';
    const KEY_MESSAGE = 'message';

    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    public function getType(): string;
    public function getMessage(): string;
    public function withMessage(string $message): MessageInterface;
    public function isError(): bool;
    public function isWarning(): bool;
    public function isInfo(): bool;
    public function getHash(): string;
}
