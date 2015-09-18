<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Type\IType;

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
    public function getName();

    /**
     * Gets the label of the column
     *
     * @return string
     */
    public function getLabel();

    /**
     * Gets the column components.
     *
     * @return IColumnComponent[]
     */
    public function getComponents();

    /**
     * Gets the column component names.
     *
     * @return string[]
     */
    public function getComponentNames();

    /**
     * @param string $componentName
     *
     * @return IColumnComponent
     * @throws bool
     */
    public function hasComponent($componentName);

    /**
     * @param string $componentName
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    public function getComponent($componentName = null);

    /**
     * Gets the component id for the supplied component name.
     *
     * @param string $componentName
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function getComponentId($componentName = null);

    /**
     * Returns whether the column only contains a single
     * component.
     *
     * @return bool
     */
    public function hasSingleComponent();
}