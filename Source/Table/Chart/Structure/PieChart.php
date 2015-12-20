<?php

namespace Dms\Core\Table\Chart\Structure;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\Chart\IChartAxis;

/**
 * The pie chart structure class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PieChart extends ChartStructure
{
    /**
     * @var IChartAxis
     */
    protected $typeAxis;

    /**
     * @var IChartAxis
     */
    protected $valueAxis;

    /**
     * @inheritDoc
     */
    public function __construct(IChartAxis $typeAxis, IChartAxis $valueAxis)
    {
        InvalidArgumentException::verify(
                count($typeAxis->getComponents()) === 1,
                'type axis must contain only one component, %d given',
                count($typeAxis->getComponents())
        );
        InvalidArgumentException::verify(
                count($valueAxis->getComponents()) === 1,
                'type axis must contain only one component, %d given',
                count($valueAxis->getComponents())
        );

        parent::__construct([$typeAxis, $valueAxis]);

        $this->typeAxis  = $typeAxis;
        $this->valueAxis = $valueAxis;
    }

    /**
     * @return IChartAxis
     */
    public function getTypeAxis()
    {
        return $this->typeAxis;
    }

    /**
     * @return IChartAxis
     */
    public function getValueAxis()
    {
        return $this->valueAxis;
    }
}