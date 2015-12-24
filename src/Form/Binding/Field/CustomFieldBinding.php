<?php

namespace Dms\Core\Form\Binding\Field;

/**
 * The custom property binding class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomFieldBinding extends FieldBinding
{
    /**
     * @var callable
     */
    protected $getterCallback;

    /**
     * @var callable
     */
    protected $setterCallback;

    /**
     * @inheritDoc
     */
    public function __construct($fieldName, $objectType, callable $getterCallback, callable $setterCallback)
    {
        parent::__construct($fieldName, $objectType);
        $this->getterCallback = $getterCallback;
        $this->setterCallback = $setterCallback;
    }

    /**
     * @inheritDoc
     */
    protected function getFieldValueFrom($object)
    {
        return call_user_func($this->getterCallback, $object);
    }

    /**
     * @inheritDoc
     */
    protected function bindFieldValueTo($object, $processedFieldValue)
    {
        call_user_func($this->setterCallback, $object, $processedFieldValue);
    }
}