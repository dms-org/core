<?php

namespace Iddigital\Cms\Core\Form\Binding;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The form binding class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormBinding implements IFormBinding
{
    /**
     * @var IForm
     */
    protected $form;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var IFieldBinding[]
     */
    protected $fieldBindings = [];

    /**
     * FormBinding constructor.
     *
     * @param IForm           $form
     * @param string          $objectType
     * @param IFieldBinding[] $fieldBindings
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IForm $form, $objectType, array $fieldBindings)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'fieldBindings', $fieldBindings, IFieldBinding::class);

        $this->form          = $form;
        $this->objectType    = $objectType;

        foreach ($fieldBindings as $fieldBinding) {
            $fieldName = $fieldBinding->getFieldName();

            if (!$this->form->hasField($fieldName)) {
                throw InvalidArgumentException::format(
                        'Invalid form binding passed to %s: invalid field, expecting one of (%s), \'%s\' given',
                        __METHOD__, Debug::formatValues($this->form->getFieldNames()), $fieldName
                );
            }

            if (!is_a($this->objectType, $fieldBinding->getObjectType(), true)) {
                throw InvalidArgumentException::format(
                        'Invalid form binding passed to %s: invalid object type, must be compatible with \'%s\', \'%s\' given',
                        __METHOD__, $this->objectType, $fieldName
                );
            }

            $this->fieldBindings[$fieldName] = $fieldBinding;
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @inheritDoc
     */
    public function getForm($object = null)
    {
        if ($object) {
            if (!($object instanceof $this->objectType)) {
                throw TypeMismatchException::argument(__METHOD__, 'object', $object, $this->objectType);
            }

            $formValues = [];

            foreach ($this->fieldBindings as $fieldName => $binding) {
                $formValues[$fieldName] = $binding->getFieldValueFromObject($object);
            }

            return $this->form->withInitialValues($formValues);
        }

        return $this->form;
    }

    /**
     * @inheritDoc
     */
    public function bindTo($object, array $formSubmission)
    {
        if (!($object instanceof $this->objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $object, $this->objectType);
        }

        $processedSubmission = $this->form->process($formSubmission);

        foreach ($this->fieldBindings as $fieldName => $binding) {
            $binding->bindFieldValueToObject($object, $processedSubmission[$fieldName]);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasFieldBinding($name)
    {
        return isset($this->fieldBindings[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getFieldBinding($name)
    {
        if (!isset($this->fieldBindings[$name])) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: invalid form field name, expecting one of (%s), \'%s\' given',
                    __METHOD__, Debug::formatValues(array_keys($this->fieldBindings)), $name
            );
        }

        return $this->fieldBindings[$name];
    }

    /**
     * @inheritDoc
     */
    public function getFieldBindings()
    {
        return $this->fieldBindings;
    }
}