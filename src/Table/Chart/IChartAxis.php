<?php declare(strict_types = 1);

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
    public function getName() : string;

    /**
     * @return string
     */
    public function getLabel() : string;

    /**
     * @return IColumnComponentType
     */
    public function getType() : IColumnComponentType;

    /**
     * @return IColumnComponent[]
     */
    public function getComponents() : array;

    /**
     * @param string|null $name
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    public function getComponent(string $name = null) : IColumnComponent;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasComponent(string $name) : bool;
}