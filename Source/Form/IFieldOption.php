<?php
namespace Iddigital\Cms\Core\Form;

/**
 * The field option interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldOption
{
    /**
     * Gets the option value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Gets the option label.
     *
     * @return string
     */
    public function getLabel();
}