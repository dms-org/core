<?php

namespace Dms\Core\Table\Chart\Structure;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\Chart\IChartAxis;

/**
 * The graph chart structure base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class GraphChart extends ChartStructure
{
    /**
     * @var IChartAxis
     */
    private $horizontalAxis;

    /**
     * @var IChartAxis
     */
    private $verticalAxis;

    /**
     * @param IChartAxis $horizontalAxisComponent
     * @param IChartAxis $verticalAxisComponent
     */
    public function __construct(IChartAxis $horizontalAxisComponent, IChartAxis $verticalAxisComponent)
    {
        InvalidArgumentException::verify(
                count($horizontalAxisComponent->getComponents()) === 1,
                'horizontal axis must contain only one component, %d given',
                count($horizontalAxisComponent->getComponents())
        );

        parent::__construct([$horizontalAxisComponent, $verticalAxisComponent]);
        $this->horizontalAxis = $horizontalAxisComponent;
        $this->verticalAxis = $verticalAxisComponent;
    }

    /**
     * @return IChartAxis
     */
    public function getHorizontalAxis()
    {
        return $this->horizontalAxis;
    }

    /**
     * @return IChartAxis
     */
    public function getVerticalAxis()
    {
        return $this->verticalAxis;
    }
}