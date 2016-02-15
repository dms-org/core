<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\Condition\ConditionOperatorType;
use Dms\Core\Model\Type\Builder\Type;

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
    public function __construct(string $typeString)
    {
        $this->typeString = $typeString;
    }

    /**
     * @return IType[]
     */
    protected function loadValidOperatorTypes() : array
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
    final public function isSupersetOf(IType $type) : bool
    {
        return $type->isSubsetOf($this);
    }

    /**
     * @inheritDoc
     */
    final public function isSubsetOf(IType $type) : bool
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
    protected function checkThisIsSubsetOf(IType $type) : bool
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
    final public function asTypeString() : string
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
    public function union(IType $type) : IType
    {
        if ($type instanceof MixedType) {
            return $type;
        }

        return $this->equals($type) ? $this : UnionType::create([$this, $type]);
    }

    /**
     * {@inheritDoc}
     */
    public function nullable() : IType
    {
        return $this->isOfType(null) ? $this : $this->union(Type::null());
    }

    /**
     * {@inheritDoc}
     */
    final public function isNullable() : bool
    {
        return $this->isOfType(null);
    }

    /**
     * {@inheritDoc}
     */
    public function nonNullable() : IType
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
    final public function equals(IType $type) : bool
    {
        // Value-wise equality
        return $this == $type;
    }

    /**
     * {@inheritDoc}
     */
    final public function getConditionOperatorTypes() : array
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
    public function getConditionOperators() : array
    {
        return array_keys($this->getConditionOperatorTypes());
    }
}