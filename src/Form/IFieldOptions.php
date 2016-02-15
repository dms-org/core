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
     * Gets the field name.
     *
     * @return IFieldOption[]
     */
    public function getAll() : array;

    /**
     * Gets all the option values.
     *
     * @return mixed
     */
    public function getAllValues();
}
