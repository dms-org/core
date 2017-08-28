<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding\Accessor;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The getter and setter method property accessor class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GetterSetterMethodAccessor extends FieldAccessor
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
    public function __construct(string $objectType, string $getterMethodName, string $setterMethodName)
    {
        parent::__construct($objectType);

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
    protected function getValueFrom($object)
    {
        return $object->{$this->getterMethodName}();
    }

    /**
     * @inheritDoc
     */
    protected function bindValueTo($object, $processedFieldValue)
    {
        $object->{$this->setterMethodName}($processedFieldValue);
    }
}