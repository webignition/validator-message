<?php

namespace webignition\ValidatorMessage;

class MessageList implements MessageListInterface
{
    /**
     * @var MessageInterface[]
     */
    private $messages = [];

    private $errorCount = 0;
    private $warningCount = 0;
    private $infoCount = 0;

    public function __construct(array $messages = [])
    {
        foreach ($messages as $message) {
            if ($message instanceof MessageInterface) {
                $this->addMessage($message);
            }
        }
    }

    public function addMessage(MessageInterface $message)
    {
        $this->messages[$message->getHash()] = $message;

        if ($message->isError()) {
            $this->errorCount++;
        } elseif ($message->isWarning()) {
            $this->warningCount++;
        } elseif ($message->isInfo()) {
            $this->infoCount++;
        }
    }

    /**
     * @return MessageInterface[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return MessageInterface[]
     */
    public function getErrors(): array
    {
        return $this->getMessagesOfType(MessageInterface::TYPE_ERROR);
    }

    /**
     * @return MessageInterface[]
     */
    public function getWarnings(): array
    {
        return $this->getMessagesOfType(MessageInterface::TYPE_WARNING);
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getWarningCount(): int
    {
        return $this->warningCount;
    }

    public function getInfoCount(): int
    {
        return $this->infoCount;
    }

    public function getMessageCount(): int
    {
        return count($this->messages);
    }

    public function mutate(callable $mutator): MessageList
    {
        return $this->map(
            function (MessageList &$messageList, MessageInterface $message) use ($mutator) {
                $messageList->addMessage($mutator($message));
            }
        );
    }

    public function filter(callable $matcher): MessageList
    {
        return $this->map(
            function (MessageList &$messageList, MessageInterface $message) use ($matcher) {
                if ($matcher($message)) {
                    $messageList->addMessage($message);
                }
            }
        );
    }

    public function contains(MessageInterface $message): bool
    {
        return array_key_exists($message->getHash(), $this->messages);
    }

    public function merge(MessageList $messageList): MessageList
    {
        $messages = array_values($this->getMessages());
        $additionalMessages = array_values($messageList->getMessages());

        foreach ($additionalMessages as $additionalMessage) {
            if (!$this->contains($additionalMessage)) {
                $messages[] = $additionalMessage;
            }
        }

        $newMessageList = new MessageList();

        foreach ($messages as $message) {
            $newMessageList->addMessage($message);
        }

        return $newMessageList;
    }

    private function map(callable $callable): MessageList
    {
        $messageList = new MessageList();
        $messages = $this->messages;

        foreach ($messages as $message) {
            $callable($messageList, $message);
        }

        return $messageList;
    }

    private function getMessagesOfType(string $type): array
    {
        $messages = [];

        foreach ($this->messages as $message) {
            if ($type === $message->getType()) {
                $messages[] = $message;
            }
        }

        return $messages;
    }
}
