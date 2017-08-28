<?php declare(strict_types=1);

namespace Dms\Core\Form\Binding\Accessor;

use Dms\Core\Exception\TypeMismatchException;


/**
 * The field accessor base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldAccessor implements IFieldAccessor
{
    /**
     * @var string
     */
    protected $objectType;

    /**
     * FieldAccessor constructor.
     *
     * @param string $objectType
     */
    public function __construct(string $objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @inheritDoc
     */
    final public function getValueFromObject($object)
    {
        if (!($object instanceof $this->objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $this->objectType, $object);
        }

        return $this->getValueFrom($object);
    }

    /**
     * @param object $object
     *
     * @return mixed
     */
    abstract protected function getValueFrom($object);

    /**
     * @inheritDoc
     */
    final public function bindValueToObject($object, $processedFieldValue)
    {
        if (!($object instanceof $this->objectType)) {
            throw TypeMismatchException::argument(__METHOD__, 'object', $this->objectType, $object);
        }

        $this->bindValueTo($object, $processedFieldValue);
    }

    /**
     * @param object $object
     * @param mixed  $processedFieldValue
     *
     * @return void
     */
    abstract protected function bindValueTo($object, $processedFieldValue);
}