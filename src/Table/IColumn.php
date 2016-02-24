<?php declare(strict_types = 1);

namespace Dms\Core\Table;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The table column interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IColumn
{
    /**
     * Gets the name of the column
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Gets the label of the column
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Returns whether the column is hidden
     *
     * @return bool
     */
    public function isHidden() : bool;

    /**
     * Gets the column components.
     *
     * @return IColumnComponent[]
     */
    public function getComponents() : array;

    /**
     * Gets the column component names.
     *
     * @return string[]
     */
    public function getComponentNames() : array;

    /**
     * @param string $componentName
     *
     * @return bool
     */
    public function hasComponent(string $componentName) : bool;

    /**
     * @param string $componentName
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    public function getComponent(string $componentName = null) : IColumnComponent;

    /**
     * Gets the component id for the supplied component name.
     *
     * @param string $componentName
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getComponentId(string $componentName = null) : string;

    /**
     * Returns whether the column only contains a single
     * component.
     *
     * @return bool
     */
    public function hasSingleComponent() : bool;
}