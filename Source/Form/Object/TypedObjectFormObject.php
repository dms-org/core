<?php

namespace Iddigital\Cms\Core\Form\Object;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The typed object form object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TypedObjectFormObject extends IndependentFormObject
{
    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var ITypedObject|null
     */
    private $typedObject;

    public function __construct(ITypedObject $object = null)
    {
        $this->objectClass = $this->objectType();
        $this->typedObject = $object;

        if ($object) {
            $this->verifyObject($object);
        }

        parent::__construct();

        if ($object) {
            $this->populateForm($object);
        }
    }

    /**
     * @return ITypedObject|null
     */
    final public function getObject()
    {
        return $this->typedObject;
    }

    /**
     * @inheritDoc
     */
    final protected function defineForm(FormObjectDefinition $form)
    {
        $form->getClass()->property($this->objectClass)->ignore();
        $form->getClass()->property($this->typedObject)->ignore();

        $this->defineFormObject($form);
    }

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     *
     * @return void
     */
    abstract protected function defineFormObject(FormObjectDefinition $form);

    /**
     * Gets the type of object of this form.
     *
     * @return string
     */
    abstract protected function objectType();

    /**
     * Populates the form with the object's values.
     *
     * @param ITypedObject $object
     *
     * @return void
     */
    final public function populateForm(ITypedObject $object)
    {
        $this->verifyObject($object);
        $this->typedObject = $object;
        $this->populateFormWithobject($object);
    }

    /**
     * Populates the form with the object's values.
     *
     * @param ITypedObject $object
     *
     * @return string
     */
    abstract protected function populateFormWithObject(ITypedObject $object);

    /**
     * @return string
     */
    final public function getObjectType()
    {
        return $this->objectClass;
    }

    /**
     * @param ITypedObject $object
     *
     * @return void
     */
    final protected function verifyObject(ITypedObject $object)
    {
        InvalidArgumentException::verifyInstanceOf(__METHOD__, 'object', $object, $this->objectClass);
    }
}