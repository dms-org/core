<?php

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
    public function attrs();

    /**
     * Returns whether the type has an attribute set.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function has($attribute);

    /**
     * Gets the value of the attribute or null if not set.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function get($attribute);

    /**
     * Returns an instance of the type with the supplied attribute.
     *
     * @param string $attribute
     * @param  mixed $value
     *
     * @return static
     */
    public function with($attribute, $value);

    /**
     * Gets the type as the equivalent php type.
     *
     * @return IPhpType
     */
    public function getPhpTypeOfInput();

    /**
     * Gets the field processors of the type.
     *
     * @return IFieldProcessor[]
     */
    public function getProcessors();

    /**
     * Gets the php of the processed data.
     *
     * @return IPhpType
     */
    public function getProcessedPhpType();
}
