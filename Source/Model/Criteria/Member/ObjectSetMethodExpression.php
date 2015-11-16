<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\IMemberExpressionParser;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\CollectionType;
use Iddigital\Cms\Core\Model\Type\IType;

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