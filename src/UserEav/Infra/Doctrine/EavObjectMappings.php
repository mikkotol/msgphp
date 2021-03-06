<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Doctrine;

use MsgPhp\Domain\Infra\Doctrine\{MappingConfig, ObjectMappingProviderInterface};
use MsgPhp\User\Entity\{Fields, UserAttributeValue};

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class EavObjectMappings implements ObjectMappingProviderInterface
{
    public static function provideObjectMappings(MappingConfig $config): iterable
    {
        yield Fields\AttributeValuesField::class => [
            'attributeValues' => [
                'type' => self::TYPE_ONE_TO_MANY,
                'targetEntity' => UserAttributeValue::class,
                'mappedBy' => 'user',
            ],
        ];
    }
}
