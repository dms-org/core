<?php

namespace Dms\Core\Form\Binding\Field;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;

/**
 * The getter and setter method property binding class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GetterSetterMethodBinding extends FieldBinding
{
    /**
     * @var string
     */
    protected $getterMethodName;

    /**
     * @var string
     */
    protected $setterMethodName;

    /**
     * @inheritDoc
     */
    public function __construct($fieldName, $objectType, $getterMethodName, $setterMethodName)
    {
        parent::__construct($fieldName, $objectType);

        foreach ([$getterMethodName, $setterMethodName] as $methodName) {
            if (!method_exists($objectType, $methodName)) {
                throw InvalidArgumentException::format(
                        'Invalid call to %s: method %s::%s does not exist',
                        __METHOD__, $objectType, $methodName
                );
            }
        }

        $this->getterMethodName = $getterMethodName;
        $this->setterMethodName = $setterMethodName;
    }

    /**
     * @inheritDoc
     */
    protected function getFieldValueFrom($object)
    {
        return $object->{$this->getterMethodName}();
    }

    /**
     * @inheritDoc
     */
    protected function bindFieldValueTo($object, $processedFieldValue)
    {
        $object->{$this->setterMethodName}($processedFieldValue);
    }
}