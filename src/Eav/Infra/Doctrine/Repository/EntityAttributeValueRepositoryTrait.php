<?php

declare(strict_types=1);

namespace MsgPhp\Eav\Infra\Doctrine\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use MsgPhp\Domain\Infra\Doctrine\DomainEntityRepositoryTrait;
use MsgPhp\Eav\AttributeIdInterface;
use MsgPhp\Eav\Entity\AttributeValue;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait EntityAttributeValueRepositoryTrait
{
    use DomainEntityRepositoryTrait;

    private $attributeAlias = 'attribute';
    private $attributeValueAlias = 'attribute_value';
    private $attributeValueField = 'attributeValue';

    private function addAttributeCriteria(QueryBuilder $qb, AttributeIdInterface $attributeId, $value = null): void
    {
        $field = $this->getAlias().'.'.$this->attributeValueField;
        $targetClass = $this->em->getClassMetadata($this->class)->getAssociationMapping($this->attributeValueField)['targetEntity'];

        if (!is_subclass_of($targetClass, AttributeValue::class)) {
            throw new \LogicException(sprintf('The field "%s" is expected to be an association mapping for "%s", got "%s".', $this->class.'.'.$this->attributeValueField, AttributeValue::class, $targetClass));
        }

        if (3 === \func_num_args()) {
            $qb->join($field, $this->attributeValueAlias, Join::WITH, $this->attributeValueAlias.'.checksum = '.$this->addFieldParameter($qb, $this->attributeValueField, $targetClass::getChecksum($value)));
        } else {
            $qb->join($field, $this->attributeValueAlias);
        }

        $qb->join($this->attributeValueAlias.'.attribute', $this->attributeAlias, Join::WITH, $this->attributeAlias.'.id = '.$this->addFieldParameter($qb, 'attribute', $attributeId));
    }
}
