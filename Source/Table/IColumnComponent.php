<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Form\IField;

/**
 * The table column component interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IColumnComponent
{
    /**
     * Gets the name of the column component
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the label of the column
     *
     * @return string
     */
    public function getLabel();

    /**
     * Gets the type of the column component.
     *
     * @return IColumnComponentType
     */
    public function getType();
}