<?php

namespace Dms\Core\Table\Chart\Structure;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\Chart\IChartAxis;
use Dms\Core\Table\Chart\IChartStructure;
use Dms\Core\Util\Debug;

/**
 * The chart structure base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ChartStructure implements IChartStructure
{
    /**
     * @var IChartAxis[]
     */
    protected $axes = [];

    /**
     * ChartStructure constructor.
     *
     * @param IChartAxis[] $axes
     */
    public function __construct(array $axes)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'axes', $axes, IChartAxis::class);

        foreach ($axes as $axis) {
            $this->axes[$axis->getName()] = $axis;
        }
    }

    /**
     * @inheritDoc
     */
    final public function getAxes()
    {
        return $this->axes;
    }

    /**
     * @inheritDoc
     */
    final public function getAxis($name)
    {
        if (!isset($this->axes[$name])) {
            throw InvalidArgumentException::format(
                    'Invalid chart axis name: expecting one of (%s), %s given',
                    Debug::formatValues(array_keys($this->axes)), $name
            );
        }

        return $this->axes[$name];
    }

    /**
     * @inheritDoc
     */
    final public function hasAxis($name)
    {
        return isset($this->axes[$name]);
    }
}