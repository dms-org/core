<?php declare(strict_types = 1);

namespace Dms\Core\Table;

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
    public function getName() : string;

    /**
     * Gets the label of the column component
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Gets the type of the column component.
     *
     * @return IColumnComponentType
     */
    public function getType() : IColumnComponentType;
}