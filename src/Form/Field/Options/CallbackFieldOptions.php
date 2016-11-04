<?php

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\IFieldOption;
use Dms\Core\Form\IFieldOptions;

/**
 * The call back field options class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CallbackFieldOptions implements IFieldOptions
{
    /**
     * Returns an array of field options  based on the supplied string filter.
     *
     * @var callable
     */
    protected $fieldOptionsLoader;

    /**
     * Returns a specific field options from the supplied value
     *
     * @var callable|null
     */
    protected $specificFieldOptionLoader;

    /**
     * CallbackFieldOptions constructor.
     *
     * @param callable $fieldOptionsLoader
     * @param callable|null $specificFieldOptionLoader
     */
    public function __construct(callable $fieldOptionsLoader, callable $specificFieldOptionLoader = null)
    {
        $this->fieldOptionsLoader        = $fieldOptionsLoader;
        $this->specificFieldOptionLoader = $specificFieldOptionLoader;
    }

    /**
     * Gets the available field options
     *
     * @return IFieldOption[]
     */
    public function getAll() : array
    {
        $fieldOptions = call_user_func($this->fieldOptionsLoader);
        TypeMismatchException::verifyAllInstanceOf(__METHOD__, '<return>', $fieldOptions, IFieldOption::class);
        return $fieldOptions;
    }

    /**
     * Gets all the option values.
     *
     * @return mixed[]
     */
    public function getAllValues() : array
    {
        return (new ArrayFieldOptions(call_user_func($this->fieldOptionsLoader)))->getAllValues();
    }

    /**
     * Gets all the enabled option values.
     *
     * @return mixed[]
     */
    public function getEnabledValues() : array
    {
        return (new ArrayFieldOptions(call_user_func($this->fieldOptionsLoader)))->getEnabledValues();
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
        if ($this->specificFieldOptionLoader) {
            $fieldOption = call_user_func($this->specificFieldOptionLoader, $value);

            if (!$fieldOption) {
                throw InvalidArgumentException::format('Invalid option supplied to %s: %s', __METHOD__, $value);
            }

            return $fieldOption;
        }

        return (new ArrayFieldOptions(call_user_func($this->fieldOptionsLoader)))->getOptionForValue($value);
    }

    /**
     * Returns whether the options are filterable.
     *
     * @return bool
     */
    public function canFilterOptions() : bool
    {
        return true;
    }

    /**
     * Returns whether the options are filterable.
     *
     * @param string $filter
     *
     * @return IFieldOption[]
     * @throws InvalidOperationException
     */
    public function getFilteredOptions(string $filter) : array
    {
        $options = call_user_func($this->fieldOptionsLoader, $filter);
        return (new ArrayFieldOptions($options))->getAll();
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count()
    {
        return count($this->getAll());
    }
}