<?php

namespace Dms\Core\Form\Object;

use Dms\Core\Model\IValueObject;
use Dms\Core\Model\ITypedObject;

/**
 * The value object form object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ValueObjectFormObject extends TypedObjectFormObject
{
    public function __construct(IValueObject $valueObject = null)
    {
        parent::__construct($valueObject);
    }

    /**
     * @inheritDoc
     */
    final protected function objectType()
    {
        return $this->valueObjectType();
    }

    /**
     * {@inheritDoc}
     */
    final protected function populateFormWithObject(ITypedObject $object)
    {
        /** @var IValueObject $object */
        $this->populateFormWithValueObject($object);
    }

    /**
     * Creates a value object with the form's values.
     *
     * @return IValueObject
     */
    final public function populateValueObject()
    {
        return $this->populateValueObjectFromForm();
    }

    /**
     * Gets the type of value object of this form.
     *
     * @return string
     */
    abstract protected function valueObjectType();

    /**
     * Populates the form with the value object's values.
     *
     * @param IValueObject $valueObject
     *
     * @return void
     */
    abstract protected function populateFormWithValueObject(IValueObject $valueObject);

    /**
     * Creates a value object with the form's values.
     *
     * @return IValueObject
     */
    abstract protected function populateValueObjectFromForm();
}