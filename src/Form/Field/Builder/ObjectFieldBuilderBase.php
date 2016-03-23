<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Options\ObjectIdentityOptions;

/**
 * The object field builder base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectFieldBuilderBase extends FieldBuilderBase
{
    /**
     * Labels the object options with values of the supplied member expression.
     *
     * @param string $memberExpression
     *
     * @return static
     */
    public function labelledBy(string $memberExpression)
    {
        $this->updateOptions(function (ObjectIdentityOptions $options) use ($memberExpression) {
            return $options->withLabelMemberExpression($memberExpression);
        });

        return $this;
    }

    /**
     * Labels the object options with the returned values of the supplied callback.
     *
     * Example:
     * <code>
     * ->labelledByCallback(function (SomeObject $object) : string {
     *      return $object->string;
     * })
     * </code>
     *
     * @param callable $labelCallback
     *
     * @return static
     */
    public function labelledByCallback(callable $labelCallback)
    {
        $this->updateOptions(function (ObjectIdentityOptions $options) use ($labelCallback) {
            return $options->withLabelCallback($labelCallback);
        });

        return $this;
    }

    /**
     * Enables the options according to the supplied callback.
     *
     * Example:
     * <code>
     * ->enabledWhen(function (SomeObject $object) : bool {
     *      return $object->satisfiesCondition();
     * })
     * </code>
     *
     * @param callable $enabledCallback
     *
     * @return static
     */
    public function enabledWhen(callable $enabledCallback)
    {
        $this->updateOptions(function (ObjectIdentityOptions $options) use ($enabledCallback) {
            return $options->withEnabledCallback($enabledCallback);
        });

        return $this;
    }

    /**
     * Changes the labels of the disabled items with the supplied callback.
     *
     * Example:
     * <code>
     * ->withDisabledLabels(function (string $originalLabel) : string {
     *      return 'Disabled: ' . $originalLabel;
     * })
     * </code>
     *
     * @param callable $disabledLabelCallback
     *
     * @return static
     */
    public function withDisabledLabels(callable $disabledLabelCallback)
    {
        $this->updateOptions(function (ObjectIdentityOptions $options) use ($disabledLabelCallback) {
            return $options->withDisabledLabelCallback($disabledLabelCallback);
        });

        return $this;
    }

    /**
     * @param callable $callback
     */
    abstract protected function updateOptions(callable $callback);
}