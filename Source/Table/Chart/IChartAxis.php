<?php

namespace Iddigital\Cms\Core\Table\Chart;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\IColumnComponentType;

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