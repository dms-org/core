<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding;

use Dms\Core\Exception\TypeMismatchException;

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
    public function getFieldName() : string;

    /**
     * Gets the expected object type.
     *
     * @return string
     */
    public function getObjectType() : string;

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