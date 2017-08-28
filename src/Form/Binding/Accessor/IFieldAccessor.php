<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding\Accessor;

use Dms\Core\Exception\TypeMismatchException;

/**
 * The field accessor interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldAccessor
{
    /**
     * Gets the supported object type.
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function getObjectType() : string;

    /**
     * Gets the value from the supplied object.
     *
     * @param mixed $object
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function getValueFromObject($object);

    /**
     * Binds the value to the supplied object.
     *
     * @param mixed $object
     * @param mixed $processedFieldValue
     *
     * @return mixed
     * @throws TypeMismatchException
     */
    public function bindValueToObject($object, $processedFieldValue);
}