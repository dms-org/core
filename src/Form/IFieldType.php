<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Model\Type\IType as IPhpType;

/**
 * The field type interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFieldType
{
    /**
     * Gets all the types attributes.
     *
     * @return array
     */
    public function attrs() : array;

    /**
     * Returns whether the type has an attribute set.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function has(string $attribute) : bool;

    /**
     * Gets the value of the attribute or null if not set.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function get(string $attribute);

    /**
     * Gets the values of the attributes as an array indexed by the attribute name.
     *
     * @param string[] $attributes
     *
     * @return array
     */
    public function getAll(array $attributes) : array;

    /**
     * Returns an instance of the type with the supplied attribute.
     *
     * @param string $attribute
     * @param  mixed $value
     *
     * @return static
     */
    public function with(string $attribute, $value);

    /**
     * Returns an instance of the type with the supplied attributes.
     *
     * @param array $attributes
     *
     * @return static
     */
    public function withAll(array $attributes);

    /**
     * Gets the type as the equivalent php type.
     *
     * @return IPhpType
     */
    public function getPhpTypeOfInput() : \Dms\Core\Model\Type\IType;

    /**
     * Gets the field processors of the type.
     *
     * @return IFieldProcessor[]
     */
    public function getProcessors() : array;

    /**
     * Gets the php of the processed data.
     *
     * @return IPhpType
     */
    public function getProcessedPhpType() : \Dms\Core\Model\Type\IType;
}
