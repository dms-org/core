<?php declare(strict_types = 1);

namespace Dms\Core\Module\Chart;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\IChartView;
use Dms\Core\Table\Chart\IChartDataSource;
use Dms\Core\Util\Debug;

/**
 * The chart display class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartDisplay implements IChartDisplay
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var IChartDataSource
     */
    protected $dataSource;

    /**
     * @var IChartView[]
     */
    protected $views = [];

    /**
     * ChartDisplay constructor.
     *
     * @param string           $name
     * @param IChartDataSource $dataSource
     * @param IChartView[]     $views
     */
    public function __construct(string $name, IChartDataSource $dataSource, array $views)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'views', $views, IChartView::class);

        $this->name       = $name;
        $this->dataSource = $dataSource;

        foreach ($views as $view) {
            $this->views[$view->getName()] = $view;
        }
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return IChartDataSource
     */
    public function getDataSource() : \Dms\Core\Table\Chart\IChartDataSource
    {
        return $this->dataSource;
    }

    /**
     * @return IChartView|null
     */
    public function getDefaultView() : \Dms\Core\Module\IChartView
    {
        foreach ($this->views as $view) {
            if ($view->isDefault()) {
                return $view;
            }
        }

        return reset($this->views) ?: ChartView::createDefault();
    }

    /**
     * @return IChartView[]
     */
    public function getViews() : array
    {
        return $this->views;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasView(string $name) : bool
    {
        return isset($this->views[$name]);
    }

    /**
     * @param string $name
     *
     * @return IChartView
     * @throws InvalidArgumentException
     */
    public function getView(string $name) : \Dms\Core\Module\IChartView
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        throw InvalidArgumentException::format(
                'Invalid call to %s: invalid view name, expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->views)), $name
        );
    }
}