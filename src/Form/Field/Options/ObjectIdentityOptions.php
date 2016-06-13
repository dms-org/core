<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IFieldOption;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\TypedObject;

/**
 * The object identity class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectIdentityOptions implements IFieldOptions
{
    /**
     * @var IIdentifiableObjectSet
     */
    protected $objects;

    /**
     * @var callable
     */
    protected $labelCallback;

    /**
     * @var string|null
     */
    protected $labelMemberExpression;

    /**
     * @var callable|null
     */
    protected $enabledCallback;

    /**
     * @var callable|null
     */
    protected $disabledLabelCallback;

    /**
     * EntityIdOptions constructor.
     *
     * @param IIdentifiableObjectSet $objects
     * @param callable|null          $labelCallback
     * @param string|null            $labelMemberExpression
     * @param callable|null          $enabledCallback
     * @param callable|null          $disabledLabelCallback
     */
    public function __construct(
            IIdentifiableObjectSet $objects,
            callable $labelCallback = null,
            string $labelMemberExpression = null,
            callable $enabledCallback = null,
            callable $disabledLabelCallback = null
    ) {
        $this->objects       = $objects;
        $this->labelCallback = $labelCallback;

        if ($labelMemberExpression) {
            $this->loadLabelCallbackFromMemberExpression($labelMemberExpression);
        }

        $this->enabledCallback           = $enabledCallback;
        $this->disabledLabelCallback     = $disabledLabelCallback;
    }

    protected function loadLabelCallbackFromMemberExpression(string $labelMemberExpression)
    {
        $this->labelMemberExpression = $labelMemberExpression;
        /** @var TypedObject|string $objectType */
        $objectType = $this->objects->getObjectType();
        $callback   = $this->objects->criteria()->getMemberExpressionParser()
                ->parse($objectType::definition(), $labelMemberExpression)
                ->makeArrayGetterCallable();

        $this->labelCallback = function ($object) use ($callback) {
            return $callback([$object])[0];
        };
    }

    /**
     * @return IIdentifiableObjectSet
     */
    public function getObjects() : IIdentifiableObjectSet
    {
        return $this->objects;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() : array
    {
        $options = [];

        foreach ($this->objects->getAll() as $index => $object) {
            $id        = $this->getObjectIdentity($index, $object);
            $options[] = $this->getFieldOptionForObject($object, $id);
        }

        return $options;
    }

    protected function getFieldOptionForObject(ITypedObject $object, $id) : FieldOption
    {
        $label    = (string)($this->labelCallback ? call_user_func($this->labelCallback, $object) : $id);
        $disabled = $this->enabledCallback ? !call_user_func($this->enabledCallback, $object) : false;

        if ($disabled && $this->disabledLabelCallback) {
            $label = call_user_func($this->disabledLabelCallback, $label);
        }

        return new FieldOption($id, $label, $disabled);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllValues() : array
    {
        $ids     = [];
        $objects = $this->objects->getAll();

        foreach ($objects as $index => $object) {
            $ids[] = $this->getObjectIdentity($index, $object);
        }

        return $ids;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnabledValues() : array
    {
        if ($this->enabledCallback) {
            $values = [];

            foreach ($this->getAll() as $option) {
                $values[] = $option->getValue();
            }

            return $values;
        } else {
            return $this->getAllValues();
        }
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return $this->objects->count();
    }

    /**
     * @inheritDoc
     */
    public function getOptionForValue($value) : IFieldOption
    {
        $object = $this->objects->tryGet($value);
        InvalidArgumentException::verify($object !== null, 'Invalid value supplied to %s: %s', __METHOD__, $value);

        return $this->getFieldOptionForObject($object, $value);
    }
    
    /**
     * @param callable $callback
     *
     * @return static
     */
    public function withLabelCallback(callable $callback)
    {
        $clone = clone $this;

        $clone->labelCallback         = $callback;
        $clone->labelMemberExpression = null;

        return $clone;
    }

    /**
     * @param string $memberExpression
     *
     * @return static
     */
    public function withLabelMemberExpression(string $memberExpression)
    {
        $clone = clone $this;

        $clone->loadLabelCallbackFromMemberExpression($memberExpression);

        return $clone;
    }

    /**
     * @param callable $enabledCallback
     *
     * @return static
     */
    public function withEnabledCallback(callable $enabledCallback)
    {
        $clone = clone $this;

        $clone->enabledCallback = $enabledCallback;

        return $clone;
    }

    /**
     * @param callable $disabledLabelCallback
     *
     * @return static
     */
    public function withDisabledLabelCallback(callable $disabledLabelCallback)
    {
        $clone = clone $this;

        $clone->disabledLabelCallback = $disabledLabelCallback;

        return $clone;
    }

    /**
     * @param int    $index
     * @param object $object
     *
     * @return int
     */
    abstract protected function getObjectIdentity(int $index, $object) : int;
}