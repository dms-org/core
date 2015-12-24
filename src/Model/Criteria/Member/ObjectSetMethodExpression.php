<?php

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\IMemberExpressionParser;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\CollectionType;
use Dms\Core\Model\Type\IType;

/**
 * The object set method expression base class class.
 *
 * @see    IObjectSet
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectSetMethodExpression extends MethodExpression
{
    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var FinalizedClassDefinition
     */
    protected $objectDefinition;

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, $name, array $arguments, IType $returnType)
    {
        parent::__construct($sourceType, $name, $arguments, $returnType);
    }


    protected function verifyCompatibleWith(IType $source)
    {
        if (!($source instanceof CollectionType) || !$source->getElementType()->isSubsetOf(Type::object(TypedObject::class))) {
            throw InvalidArgumentException::format(
                    'Invalid source type: expecting type compatible with %s, %s given',
                    IObjectSet::class, $source->asTypeString()
            );
        }

        /** @var string|TypedObject $objectType */
        $objectType             = $source->getElementType()->asTypeString();
        $this->objectType       = $objectType;
        $this->objectDefinition = $objectType::definition();
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getObjectDefinition()
    {
        return $this->objectDefinition;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }


}