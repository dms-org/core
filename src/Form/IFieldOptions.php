<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;

/**
 * The field options interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFieldOptions extends \Countable
{
    /**
     * Gets the available field options
     *
     * @return IFieldOption[]
     */
    public function getAll() : array;

    /**
     * Gets all the option values.
     *
     * @return mixed[]
     */
    public function getAllValues() : array;

    /**
     * Gets all the enabled option values.
     *
     * @return mixed[]
     */
    public function getEnabledValues() : array;

    /**
     * Gets the field option with the supplied value.
     *
     * @param mixed $value
     *
     * @return IFieldOption
     * @throws InvalidArgumentException
     */
    public function getOptionForValue($value) : IFieldOption;

    /**
     * Gets the field options for the supplied values.
     * Invalid values will be ignored.
     *
     * @param array $values
     *
     * @return IFieldOption[]
     */
    public function tryGetOptionsForValues(array $values) : array;

    /**
     * Returns whether the options are filterable.
     *
     * @return bool
     */
    public function canFilterOptions() : bool;

    /**
     * Returns whether the options are filterable.
     *
     * @param string $filter
     *
     * @return IFieldOption[]
     * @throws InvalidOperationException
     */
    public function getFilteredOptions(string $filter) : array;
}
