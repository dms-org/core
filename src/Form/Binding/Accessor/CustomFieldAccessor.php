<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding\Accessor;

/**
 * The custom property accessor class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomFieldAccessor extends FieldAccessor
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
    public function __construct(string $objectType, callable $getterCallback, callable $setterCallback)
    {
        parent::__construct($objectType);
        $this->getterCallback = $getterCallback;
        $this->setterCallback = $setterCallback;
    }

    /**
     * @inheritDoc
     */
    protected function getValueFrom($object)
    {
        return call_user_func($this->getterCallback, $object);
    }

    /**
     * @inheritDoc
     */
    protected function bindValueTo($object, $processedFieldValue)
    {
        call_user_func($this->setterCallback, $object, $processedFieldValue);
    }

}