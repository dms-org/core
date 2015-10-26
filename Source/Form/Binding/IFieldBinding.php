<?php

namespace Iddigital\Cms\Core\Form\Binding;

use Iddigital\Cms\Core\Exception\TypeMismatchException;

/**
 * The field binding interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldBinding
{
    /**
     * Gets the bound field name.
     *
     * @return string
     */
    public function getFieldName();

    /**
     * Gets the expected object type.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Gets processed field value from the supplied object.
     *
     * @param mixed $object
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function getFieldValueFromObject($object);

    /**
     * Binds the processed field value on the supplied object.
     *
     * @param mixed $object
     * @param mixed $processedFieldValue
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function bindFieldValueToObject($object, $processedFieldValue);
}