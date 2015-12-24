<?php

namespace Dms\Core\Table\Chart;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IColumnComponentType;

/**
 * The chart axis interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IChartAxis extends IColumn
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return IColumnComponentType
     */
    public function getType();

    /**
     * @return IColumnComponent[]
     */
    public function getComponents();

    /**
     * @param string|null $name
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    public function getComponent($name = null);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasComponent($name);
}