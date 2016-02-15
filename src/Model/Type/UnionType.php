<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\Condition\ConditionOperatorType;

/**
 * The union type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UnionType extends BaseType
{
    /**
     * @var UnionType[]
     */
    private static $cache = [];

    /**
     * @var IType[]
     */
    private $types;

    // Should not be used, use ::create
    public function __construct(array $types, $typeString)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'types', $types, IType::class);
        InvalidArgumentException::verify(count($types) > 1, 'union must contain at least two types');

        $this->types = $types;
        parent::__construct($typeString);
    }

    /**
     * @param IType[] $types
     *
     * @return IType
     */
    public static function create(array $types) : IType
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'types', $types, IType::class);

        $flatTypes = [];
        foreach ($types as $type) {
            if ($type instanceof UnionType) {
                $flatTypes += $type->getTypes();
            } else {
                $flatTypes[$type->asTypeString()] = $type;
            }
        }

        foreach ($flatTypes as $key => $type) {
            foreach ($flatTypes as $otherType) {
                if ($type->isSubsetOf($otherType) && !$type->equals($otherType)) {
                    unset($flatTypes[$key]);
                    continue 2;
                }
            }
        }

        if (count($flatTypes) === 1) {
            return reset($flatTypes);
        }

        $typeString = implode('|', array_keys($flatTypes));

        if (!isset(self::$cache[$typeString])) {
            self::$cache[$typeString] = new self($flatTypes, $typeString);
        }

        return self::$cache[$typeString];
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        $intersectionTypes = [];

        foreach ($this->types as $key => $unionedType) {
            $intersection = $unionedType->intersect($type);
            if ($intersection) {
                $intersectionTypes[] = $intersection;
            }
        }

        return $intersectionTypes ? self::create($intersectionTypes) : null;
    }

    /**
     * @inheritDoc
     */
    protected function loadValidOperatorTypes() : array
    {
        $operatorTypes = parent::loadValidOperatorTypes();
        $types         = $this->types;

        /** @var IType $firstType */
        $firstType = array_shift($types);
        /** @var ConditionOperatorType[] $operators */
        $operators = $firstType->getConditionOperatorTypes();

        foreach ($types as $type) {
            $otherOperators = $type->getConditionOperatorTypes();
            $operators      = array_intersect_key($operators, $otherOperators);

            foreach ($otherOperators as $key => $otherOperator) {
                if (isset($operators[$key])) {
                    if (in_array($key, [
                            ConditionOperator::EQUALS,
                            ConditionOperator::NOT_EQUALS,
                            ConditionOperator::IN,
                            ConditionOperator::NOT_IN
                    ])) {
                        continue;
                    }

                    $valueType = $operators[$key]->getValueType()->intersect($otherOperator->getValueType());
                    if ($valueType) {
                        $operators[$key]     = new ConditionOperatorType($key, $valueType);
                        $operatorTypes[$key] = $valueType;
                    } else {
                        unset($operators[$key], $operatorTypes[$key]);
                    }
                }
            }
        }

        return $operatorTypes;
    }

    /**
     * @param IType $type
     *
     * @return bool
     */
    public function hasUnionedType(IType $type) : bool
    {
        return isset($this->types[$type->asTypeString()]);
    }

    /**
     * Gets the unioned types.
     *
     * @return IType[]
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value) : bool
    {
        foreach ($this->types as $type) {
            if ($type->isOfType($value)) {
                return true;
            }
        }

        return false;
    }
}