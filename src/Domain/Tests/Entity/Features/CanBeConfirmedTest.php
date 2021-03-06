<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Entity\Features;

use MsgPhp\Domain\Entity\Features\CanBeConfirmed;
use MsgPhp\Domain\Event\ConfirmEvent;
use PHPUnit\Framework\TestCase;

final class CanBeConfirmedTest extends TestCase
{
    public function testConfirm(): void
    {
        $object = $this->getObject('foo', null);

        self::assertSame('foo', $object->getConfirmationToken());
        self::assertNull($object->getConfirmedAt());
        self::assertFalse($object->isConfirmed());

        $object->confirm();

        self::assertNull($object->getConfirmationToken());
        self::assertInstanceOf(\DateTimeImmutable::class, $object->getConfirmedAt());
        self::assertTrue($object->isConfirmed());
    }

    public function testHandleConfirmEvent(): void
    {
        $object = $this->getObject('foo', null);

        self::assertTrue($object->handleConfirmEvent($this->createMock(ConfirmEvent::class)));
        self::assertNull($prevToken = $object->getConfirmationToken());
        self::assertInstanceOf(\DateTimeImmutable::class, $object->getConfirmedAt());
        self::assertTrue($object->isConfirmed());
        self::assertFalse($object->handleConfirmEvent($this->createMock(ConfirmEvent::class)));
        self::assertTrue($object->isConfirmed());
    }

    private function getObject($confirmationToken, $confirmedAt)
    {
        return new class($confirmationToken, $confirmedAt) {
            use CanBeConfirmed {
                handleConfirmEvent as public;
            }

            public function __construct($confirmationToken, $confirmedAt)
            {
                $this->confirmationToken = $confirmationToken;
                $this->confirmedAt = $confirmedAt;
            }
        };
    }
}
