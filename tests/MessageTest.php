<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\ValidatorMessage\Tests;

use webignition\ValidatorMessage\AbstractMessage;
use webignition\ValidatorMessage\Tests\Implementation\ConcreteMessage;

class MessageTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = AbstractMessage::TYPE_ERROR;
        $message = 'error message';

        $concreteMessage = new ConcreteMessage($type, $message);

        $this->assertEquals($type, $concreteMessage->getType());
        $this->assertEquals($message, $concreteMessage->getMessage());
    }

    public function testWithMessage()
    {
        $type = AbstractMessage::TYPE_ERROR;
        $originalMessage = 'original message';
        $updatedMessage = 'updated message';

        $concreteMessage = new ConcreteMessage($type, $originalMessage);
        $mutatedConcreteMessage = $concreteMessage->withMessage($updatedMessage);

        $this->assertNotSame($concreteMessage, $mutatedConcreteMessage);
        $this->assertEquals($originalMessage, $concreteMessage->getMessage());
        $this->assertEquals($updatedMessage, $mutatedConcreteMessage->getMessage());
    }

    /**
     * @dataProvider isErrorIsWarningIsInfoDataProvider
     */
    public function testIsErrorIsWarningIsInfo(
        AbstractMessage $message,
        bool $expectedIsError,
        bool $expectedIsWarning,
        bool $expectedIsInfo
    ) {
        $this->assertEquals($expectedIsError, $message->isError());
        $this->assertEquals($expectedIsWarning, $message->isWarning());
        $this->assertEquals($expectedIsInfo, $message->isInfo());
    }

    public function isErrorIsWarningIsInfoDataProvider(): array
    {
        return [
            'error' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_ERROR, ''),
                'expectedIsError' => true,
                'expectedIsWarning' => false,
                'expectedIsInfo' => false,
            ],
            'warning' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_WARNING, ''),
                'expectedIsError' => false,
                'expectedIsWarning' => true,
                'expectedIsInfo' => false,
            ],
            'info' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_INFO, ''),
                'expectedIsError' => false,
                'expectedIsWarning' => false,
                'expectedIsInfo' => true,
            ],
        ];
    }

    /**
     * @dataProvider jsonSerializeDataProvider
     */
    public function testJsonSerialize(AbstractMessage $message, array $expectedSerializedForm)
    {
        $this->assertEquals($expectedSerializedForm, $message->jsonSerialize());
    }

    public function jsonSerializeDataProvider()
    {
        return [
            'error' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_ERROR, 'error message'),
                'expectedSerializedForm' => [
                    'type' => 'error',
                    'message' => 'error message',
                ],
            ],
            'warning' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_WARNING, 'warning message'),
                'expectedSerializedForm' => [
                    'type' => 'warning',
                    'message' => 'warning message',
                ],
            ],
            'info' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_INFO, 'info message'),
                'expectedSerializedForm' => [
                    'type' => 'info',
                    'message' => 'info message',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getHashDataProvider
     */
    public function testGetHash(AbstractMessage $message, string $expectedHash)
    {
        $this->assertEquals($expectedHash, $message->getHash());
    }

    public function getHashDataProvider()
    {
        return [
            'error' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_ERROR, 'error message'),
                'expectedHash' => '878d2e9703d9aff872772f830cdd1190',
            ],
            'warning' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_WARNING, 'warning message'),
                'expectedHash' => 'e9556e684890f73b98aca2d7952588b0',
            ],
            'info' => [
                'message' => new ConcreteMessage(AbstractMessage::TYPE_INFO, 'info message'),
                'expectedHash' => '87723356a08f11580fcdd366e086fe0f',
            ],
        ];
    }
}
