<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Model\Criteria\Condition\ConditionOperatorType;

/**
 * The type interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IType
{
    const STRING = 'string';
    const INT = 'int';
    const BOOL = 'bool';
    const FLOAT = 'float';

    /**
     * Returns a union type.
     *
     * @param IType $type
     *
     * @return IType
     */
    public function union(IType $type) : IType;

    /**
     * Returns an equivalent nullable type
     *
     * @return IType
     */
    public function nullable() : IType;

    /**
     * Returns whether the type is nullable.
     *
     * @return bool
     */
    public function isNullable() : bool;

    /**
     * Returns the type as non nullable if it was nullable.
     *
     * @return IType
     */
    public function nonNullable() : IType;

    /**
     * Returns the whether the type is a superset of the supplied type.
     *
     * This will return true if the types are equal.
     *
     * @param IType $type
     *
     * @return bool
     */
    public function isSupersetOf(IType $type) : bool;

    /**
     * Returns the whether the type is a subset of the supplied type.
     *
     * This will return true if the types are equal.
     *
     * @param IType $type
     *
     * @return bool
     */
    public function isSubsetOf(IType $type) : bool;

    /**
     * Returns the intersection between the two types
     * or null if there is no intersection.
     *
     * @param IType $type
     *
     * @return IType|null
     */
    public function intersect(IType $type);

    /**
     * Returns the type string
     *
     * @return string
     */
    public function asTypeString() : string;

    /**
     * Returns whether the types are equal.
     *
     * @param IType $type
     *
     * @return bool
     */
    public function equals(IType $type) : bool;

    /**
     * Returns whether the supplied value is of this type.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isOfType($value) : bool;

    /**
     * Returns the valid condition operator types for the supplied property.
     *
     * @return ConditionOperatorType[]
     */
    public function getConditionOperatorTypes() : array;

    /**
     * Returns the valid condition operator strings for the supplied property.
     *
     * @return string[]
     */
    public function getConditionOperators() : array;
}