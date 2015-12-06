<?php

namespace Iddigital\Cms\Core\Model\Type;

use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperator;
use Iddigital\Cms\Core\Model\Criteria\Condition\ConditionOperatorType;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The base type class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class BaseType implements IType
{
    /**
     * @var string
     */
    private $typeString;

    /**
     * @var ConditionOperatorType[]|null
     */
    private $validOperatorTypes;

    /**
     * @param string $typeString
     */
    public function __construct($typeString)
    {
        $this->typeString = $typeString;
    }

    /**
     * @return IType[]
     */
    protected function loadValidOperatorTypes()
    {
        $arrayOfThis = Type::arrayOf($this);

        return [
                ConditionOperator::EQUALS     => $this,
                ConditionOperator::NOT_EQUALS => $this,
                ConditionOperator::IN         => $arrayOfThis,
                ConditionOperator::NOT_IN     => $arrayOfThis,
        ];
    }

    /**
     * @inheritDoc
     */
    final public function isSupersetOf(IType $type)
    {
        return $type->isSubsetOf($this);
    }

    /**
     * @inheritDoc
     */
    final public function isSubsetOf(IType $type)
    {
        if ($this->equals($type)) {
            return true;
        }


        return $this->checkThisIsSubsetOf($type);
    }

    /**
     * @inheritDoc
     */
    final public function isCompatibleWith(IType $type)
    {
        return $this->isSubsetOf($type);
    }

    /**
     * @param IType $type
     *
     * @return bool
     */
    protected function checkThisIsSubsetOf(IType $type)
    {
        if ($type instanceof MixedType) {
            return true;
        } elseif ($type instanceof UnionType) {
            return $type->hasUnionedType($this);
        } elseif ($type instanceof NotType) {
            return !$type->getType()->isSubsetOf($this);
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    final public function asTypeString()
    {
        return $this->typeString;
    }

    /**
     * @inheritDoc
     */
    final public function intersect(IType $type)
    {
        if ($type->isSupersetOf($this)) {
            return $this;
        } elseif ($this->isSupersetOf($type)) {
            return $type;
        }

        return $this->intersection($type);
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    abstract protected function intersection(IType $type);

    /**
     * {@inheritDoc}
     */
    public function union(IType $type)
    {
        if ($type instanceof MixedType) {
            return $type;
        }

        return $this->equals($type) ? $this : UnionType::create([$this, $type]);
    }

    /**
     * {@inheritDoc}
     */
    public function nullable()
    {
        return $this->isOfType(null) ? $this : $this->union(Type::null());
    }

    /**
     * {@inheritDoc}
     */
    final public function isNullable()
    {
        return $this->isOfType(null);
    }

    /**
     * {@inheritDoc}
     */
    public function nonNullable()
    {
        if (!($this instanceof UnionType)) {
            return $this;
        }

        $types = [];

        foreach ($this->getTypes() as $type) {
            if ($type instanceof NullType) {
                continue;
            }

            $types[] = $type;
        }

        return UnionType::create($types);
    }

    /**
     * {@inheritDoc}
     */
    final public function equals(IType $type)
    {
        // Value-wise equality
        return $this == $type;
    }

    /**
     * {@inheritDoc}
     */
    final public function getConditionOperatorTypes()
    {
        if ($this->validOperatorTypes === null) {
            $this->validOperatorTypes = [];

            foreach ($this->loadValidOperatorTypes() as $operator => $type) {
                $this->validOperatorTypes[$operator] = new ConditionOperatorType(
                        $operator,
                        $type
                );
            }
        }

        return $this->validOperatorTypes;
    }

    /**
     * @return string[]
     */
    public function getConditionOperators()
    {
        return array_keys($this->getConditionOperatorTypes());
    }
}