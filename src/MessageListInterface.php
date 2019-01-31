<?php

namespace webignition\ValidatorMessage;

interface MessageListInterface
{
    public function addMessage(MessageInterface $message);

    /**
     * @return MessageInterface[]
     */
    public function getMessages(): array;

    /**
     * @return MessageInterface[]
     */
    public function getErrors(): array;

    /**
     * @return MessageInterface[]
     */
    public function getWarnings(): array;

    public function getErrorCount(): int;
    public function getWarningCount(): int;
    public function getInfoCount(): int;
    public function getMessageCount(): int;
    public function mutate(callable $mutator): MessageList;
    public function filter(callable $matcher): MessageList;
    public function contains(MessageInterface $message): bool;
    public function merge(MessageList $messageList): MessageList;
}
