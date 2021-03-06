<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Tests\Factory;

use MsgPhp\Domain\{DomainIdentityHelper, DomainIdentityMappingInterface, DomainIdInterface};
use MsgPhp\Domain\Exception\InvalidClassException;
use MsgPhp\Domain\Factory\{DomainObjectFactoryInterface, EntityAwareFactory};
use PHPUnit\Framework\TestCase;

final class EntityAwareFactoryTest extends TestCase
{
    /** @var EntityAwareFactory */
    private $factory;

    protected function setUp(): void
    {
        $innerFactory = $this->createMock(DomainObjectFactoryInterface::class);
        $innerFactory->expects(self::any())
            ->method('create')
            ->willReturnCallback(function ($class, $context) {
                if ('id' === $class) {
                    $id = $this->createMock(DomainIdInterface::class);
                    $id->expects(self::any())
                        ->method('toString')
                        ->willReturn($context ? reset($context) : 'new');

                    return $id;
                }

                $o = new \stdClass();
                $o->class = $class;
                $o->context = $context;

                return $o;
            });
        $innerFactory->expects(self::any())
            ->method('getClass')
            ->willReturnArgument(0);

        $identityMapping = $this->createMock(DomainIdentityMappingInterface::class);
        $identityMapping->expects(self::any())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id_field', 'id_field2']);

        $this->factory = new EntityAwareFactory($innerFactory, new DomainIdentityHelper($identityMapping), ['alias_id' => 'id']);
    }

    public function testCreate(): void
    {
        self::assertInstanceOf(\stdClass::class, $object = $this->factory->create('foo'));
        self::assertSame(['class' => 'foo', 'context' => []], (array) $object);
        self::assertInstanceOf(\stdClass::class, $object = $this->factory->create('bar', ['context']));
        self::assertSame(['class' => 'bar', 'context' => ['context']], (array) $object);
    }

    public function testGetClass(): void
    {
        self::assertSame('foo', $this->factory->getClass('foo'));
        self::assertSame('bar', $this->factory->getClass('bar', ['context']));
    }

    public function testReference(): void
    {
        /** @var TestReferencedEntity $reference */
        $reference = $this->factory->reference(TestReferencedEntity::class, ['id_field' => 1, 'id_field2' => 2]);

        self::assertInstanceOf(TestReferencedEntity::class, $reference);
        self::assertSame([1, 2], $reference->get());
    }

    public function testReferenceWithUnknownClass(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->factory->reference('foo', ['id_field' => 1, 'id_field2' => 2]);
    }

    public function testReferenceWithInvalidIdentity(): void
    {
        $this->expectException(\LogicException::class);

        $this->factory->reference(TestReferencedEntity::class, 1);
    }

    public function testIdentify(): void
    {
        self::assertSame('1', $this->factory->identify('id', '1')->toString());
        self::assertSame('1', $this->factory->identify('alias_id', '1')->toString());
        self::assertSame($id = $this->createMock(DomainIdInterface::class), $this->factory->identify('id', $id));
        self::assertSame($id = $this->createMock(DomainIdInterface::class), $this->factory->identify('alias_id', $id));
    }

    public function testIdentifyWithUnknownClass(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->factory->identify('foo', '1');
    }

    public function testNextIdentifier(): void
    {
        self::assertSame('new', $this->factory->nextIdentifier('id')->toString());
        self::assertSame('new', $this->factory->nextIdentifier('alias_id')->toString());
    }

    public function testNextIdentifierWithUnknownClass(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->factory->nextIdentifier('foo');
    }
}

class TestReferencedEntity
{
    private $idField;
    private $idField2 = 'foo';

    public function __construct()
    {
        throw new \BadMethodCallException();
    }

    public function get(): array
    {
        return [$this->idField, $this->idField2];
    }
}
