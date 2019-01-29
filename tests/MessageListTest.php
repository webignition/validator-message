<?php
/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\ValidatorMessage\Tests;

use webignition\ValidatorMessage\MessageInterface;
use webignition\ValidatorMessage\MessageList;
use webignition\ValidatorMessage\Tests\Implementation\ConcreteMessage;

class MessageListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MessageList
     */
    private $messageList;

    protected function setUp()
    {
        parent::setUp();

        $this->messageList = new MessageList([
            new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error1'),
            new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning1'),
            new ConcreteMessage(MessageInterface::TYPE_INFO, 'info1'),
            new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error2'),
            new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning2'),
            new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error3'),
        ]);
    }

    /**
     * @dataProvider addMessageDataProvider
     */
    public function testAddMessage(
        MessageInterface $message,
        int $expectedErrorCount,
        int $expectedWarningCount,
        int $expectedInfoCount,
        int $expectedMessageCount
    ) {
        $this->messageList->addMessage($message);

        $this->assertEquals($expectedErrorCount, $this->messageList->getErrorCount());
        $this->assertEquals($expectedWarningCount, $this->messageList->getWarningCount());
        $this->assertEquals($expectedInfoCount, $this->messageList->getInfoCount());
        $this->assertEquals($expectedMessageCount, $this->messageList->getMessageCount());
    }

    public function addMessageDataProvider(): array
    {
        return [
            'add error' => [
                'message' => new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error4'),
                'expectedErrorCount' => 4,
                'expectedWarningCount' => 2,
                'expectedInfoCount' => 1,
                'expectedMessageCount' => 7,
            ],
            'add warning' => [
                'message' => new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning3'),
                'expectedErrorCount' => 3,
                'expectedWarningCount' => 3,
                'expectedInfoCount' => 1,
                'expectedMessageCount' => 7,
            ],
            'add info' => [
                'message' => new ConcreteMessage(MessageInterface::TYPE_INFO, 'info2'),
                'expectedErrorCount' => 3,
                'expectedWarningCount' => 2,
                'expectedInfoCount' => 2,
                'expectedMessageCount' => 7,
            ],
        ];
    }

    public function testGetMessages()
    {
        $this->assertEquals(
            [
                new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error1'),
                new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning1'),
                new ConcreteMessage(MessageInterface::TYPE_INFO, 'info1'),
                new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error2'),
                new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning2'),
                new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error3'),
            ],
            array_values($this->messageList->getMessages())
        );
    }

    public function testGetErrors()
    {
        $this->assertEquals(
            [
                new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error1'),
                new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error2'),
                new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error3'),
            ],
            $this->messageList->getErrors()
        );
    }

    public function testGetWarnings()
    {
        $this->assertEquals(
            [
                new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning1'),
                new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning2'),
            ],
            $this->messageList->getWarnings()
        );
    }

    /**
     * @dataProvider mutateDataProvider
     */
    public function testMutate(MessageList $originalMessageList, callable $mutator, array $expectedMessages)
    {
        $mutatedMessageList = $originalMessageList->mutate($mutator);
        $this->assertNotSame($originalMessageList, $mutatedMessageList);

        $mutatedMessages = array_values($mutatedMessageList->getMessages());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(count($expectedMessages), $mutatedMessages);

        foreach ($mutatedMessages as $index => $mutatedMessage) {
            $this->assertEquals($expectedMessages[$index], $mutatedMessage);
        }
    }

    public function mutateDataProvider(): array
    {
        return [
            'no messages, non-modifying mutator' => [
                'originalMessageList' => new MessageList(),
                'mutator' => function (MessageInterface $message) {
                    return $message;
                },
                'expectedMessages' => [],
            ],
            'has messages, non-modifying mutator' => [
                'originalMessageList' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ]),
                'mutator' => function (MessageInterface $message) {
                    return $message;
                },
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ],
            ],
            'has messages, non-matching modifying mutator' => [
                'originalMessageList' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ]),
                'mutator' => function (MessageInterface $message) {
                    if ($message->isWarning()) {
                        $message = $message->withMessage('updated message');
                    }

                    return $message;
                },
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ],
            ],
            'has messages, matching modifying mutator' => [
                'originalMessageList' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning message'),
                ]),
                'mutator' => function (MessageInterface $message) {
                    if ($message->isError()) {
                        $message = $message->withMessage('updated error message');
                    }

                    return $message;
                },
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'updated error message'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning message'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider filterDataProvider
     */
    public function testFilter(MessageList $originalMessageList, callable $matcher, array $expectedMessages)
    {
        $filteredMessageList = $originalMessageList->filter($matcher);
        $this->assertNotSame($originalMessageList, $filteredMessageList);

        $filteredMessages = array_values($filteredMessageList->getMessages());
        /** @noinspection PhpParamsInspection */
        $this->assertCount(count($expectedMessages), $filteredMessages);

        foreach ($filteredMessages as $index => $mutatedMessage) {
            $this->assertEquals($expectedMessages[$index], $mutatedMessage);
        }
    }

    public function filterDataProvider(): array
    {
        return [
            'no messages, non-filtering filter' => [
                'originalMessageList' => new MessageList(),
                'matcher' => function (MessageInterface $message): bool {
                    return true;
                },
                'expectedMessages' => [],
            ],
            'has messages, non-filtering filter' => [
                'originalMessageList' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ]),
                'matcher' => function (MessageInterface $message): bool {
                    return true;
                },
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ],
            ],
            'has messages, non-matching filtering filter' => [
                'originalMessageList' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ]),
                'matcher' => function (MessageInterface $message): bool {
                    return '' !== $message->getMessage();
                },
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error message'),
                ],
            ],
            'has messages, matching filtering filter' => [
                'originalMessageList' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'foo'),
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'bar'),
                ]),
                'matcher' => function (MessageInterface $message): bool {
                    return 'foo' === $message->getMessage();
                },
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'foo'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $messages, array $expectedMessages)
    {
        $messageList = new MessageList($messages);

        $this->assertEquals(
            $expectedMessages,
            array_values($messageList->getMessages())
        );
    }

    public function createDataProvider(): array
    {
        return [
            'empty' => [
                'messages' => [],
                'expectedMessages' => [],
            ],
            'non-message values' => [
                'messages' => [1, 'string', true],
                'expectedMessages' => [],
            ],
            'message values' => [
                'messages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ],
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ],
            ],
            'message and non-message values' => [
                'messages' => [
                    1,
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    'string',
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    false,
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ],
                'expectedMessages' => [
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider mergeDataProvider
     */
    public function testMerge(
        MessageList $originalMessages,
        MessageList $additionalMessages,
        MessageList $expectedMessages
    ) {
        $mergedMessages = $originalMessages->merge($additionalMessages);

        $this->assertNotSame($originalMessages, $mergedMessages);
        $this->assertEquals(
            array_values($expectedMessages->getMessages()),
            array_values($mergedMessages->getMessages())
        );
    }

    public function mergeDataProvider(): array
    {
        return [
            'empty' => [
                'originalMessages' => new MessageList(),
                'additionalMessages' => new MessageList(),
                'expectedMessages' => new MessageList(),
            ],
            'additional messages all contained in original messages' => [
                'originalMessages' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ]),
                'additionalMessages' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ]),
                'expectedMessages' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ]),
            ],
            'new messages' => [
                'originalMessages' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                ]),
                'additionalMessages' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ]),
                'expectedMessages' => new MessageList([
                    new ConcreteMessage(MessageInterface::TYPE_ERROR, 'error'),
                    new ConcreteMessage(MessageInterface::TYPE_WARNING, 'warning'),
                    new ConcreteMessage(MessageInterface::TYPE_INFO, 'info'),
                ]),
            ],
        ];
    }
}
