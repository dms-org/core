<?php declare(strict_types = 1);

namespace Dms\Core\Form;

/**
 * The field options interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFieldOptions
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
}
