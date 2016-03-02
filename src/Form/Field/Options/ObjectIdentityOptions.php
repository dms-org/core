<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Form\IFieldOptions;
use Dms\Core\Model\IIdentifiableObjectSet;
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
     * EntityIdOptions constructor.
     *
     * @param IIdentifiableObjectSet $objects
     * @param callable|null          $labelCallback
     * @param string|null            $labelMemberExpression
     */
    public function __construct(IIdentifiableObjectSet $objects, callable $labelCallback = null, string $labelMemberExpression = null)
    {
        $this->objects       = $objects;
        $this->labelCallback = $labelCallback;

        if ($labelMemberExpression) {
            $this->loadLabelCallbackFromMemberExpression($labelMemberExpression);
        }
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
            $id = $this->getObjectIdentity($index, $object);

            $options[] = new FieldOption(
                $id,
                (string)($this->labelCallback ? call_user_func($this->labelCallback, $object) : $id)
            );
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllValues()
    {
        $ids     = [];
        $objects = $this->objects->getAll();

        foreach ($objects as $index => $object) {
            $ids[] = $this->getObjectIdentity($index, $object);
        }

        return $ids;
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
     * @param int    $index
     * @param object $object
     *
     * @return int
     */
    abstract protected function getObjectIdentity(int $index, $object) : int;
}