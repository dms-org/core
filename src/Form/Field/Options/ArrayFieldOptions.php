<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Form\IFieldOption;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Util\Hashing\ValueHasher;

/**
 * The field options class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayFieldOptions implements IFieldOptions
{
    /**
     * @var IFieldOption[]
     */
    private $options;

    public function __construct(array $options)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'options', $options, IFieldOption::class);
        $this->options = $options;
    }

    /**
     * Constructs an array field options collections with the option
     * values as the array keys and the labels as the array values.
     *
     * @param array  $keyValueOptions
     * @param string $valueType
     *
     * @return ArrayFieldOptions
     */
    public static function fromAssocArray(array $keyValueOptions, string $valueType = 'string') : ArrayFieldOptions
    {
        $options = [];

        foreach ($keyValueOptions as $value => $label) {
            settype($value, $valueType);
            $options[] = $label instanceof IFieldOption
                    ? $label
                    : new FieldOption($value, $label);
        }

        return new self($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() : array
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllValues() : array
    {
        $values = [];

        foreach ($this->options as $option) {
            $values[] = $option->getValue();
        }

        return $values;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnabledValues() : array
    {
        $values = [];

        foreach ($this->options as $option) {
            if (!$option->isDisabled()) {
                $values[] = $option->getValue();
            }
        }

        return $values;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->options);
    }

    /**
     * Gets the field option with the supplied value.
     *
     * @param mixed $value
     *
     * @return IFieldOption
     * @throws InvalidArgumentException
     */
    public function getOptionForValue($value) : IFieldOption
    {
        foreach ($this->getAll() as $option) {
            if (ValueHasher::areEqual($value, $option->getValue())) {
                return $option;
            }
        }

        throw InvalidArgumentException::format('Invalid value supplied to %s', __METHOD__);
    }

    /**
     * Gets the field options for the supplied values.
     *
     * @param array $values
     *
     * @return IFieldOption[]
     */
    public function tryGetOptionsForValues(array $values): array
    {
        $optionsIndexedByHash = [];

        foreach ($this->getAll() as $option) {
            $optionsIndexedByHash[ValueHasher::hash($option->getValue())] = $option;
        }

        $options = [];

        foreach ($values as $value) {
            $hash = ValueHasher::hash($value);

            if (isset($optionsIndexedByHash[$hash])) {
                $options[] = $optionsIndexedByHash[$hash];
            }
        }

        return $options;
    }

    /**
     * Returns whether the options are filterable.
     *
     * @return bool
     */
    public function canFilterOptions() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredOptions(string $filter) : array
    {
        throw InvalidOperationException::format(__METHOD__);
    }
}