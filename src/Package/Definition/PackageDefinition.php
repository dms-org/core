<?php declare(strict_types = 1);

namespace Dms\Core\Package\Definition;

use Dms\Core\Exception\InvalidOperationException;

/**
 * The package definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $nameModuleClassMap = [];

    /**
     * @var string[]
     */
    protected $dashboardWidgetNames = [];

    /**
     * PackageDefinition constructor.
     */
    public function __construct()
    {
    }

    /**
     * Defines the name of this package.
     *
     * @param string $name
     *
     * @return void
     */
    public function name(string $name)
    {
        $this->name = $name;
    }

    /**
     * Defines the widgets contained within the package dashboard.
     *
     * Example:
     * <code>
     * $package->dashboard()
     *      ->widgets([
     *           'some-module-name.some-widget-name'
     *      ]);
     * </code>
     *
     * @return DashboardWidgetDefiner
     */
    public function dashboard() : DashboardWidgetDefiner
    {
        return new DashboardWidgetDefiner(function (array $widgetNames) {
            $this->dashboardWidgetNames = array_merge($this->dashboardWidgetNames, $widgetNames);
        });
    }

    /**
     * Defines the modules contained within this package.
     *
     * Example:
     * <code>
     * $package->modules([
     *      'some-module-name' => SomeModule::class,
     * ]);
     * </code>
     *
     * @param string[] $nameModuleClassMap
     *
     * @return void
     */
    public function modules(array $nameModuleClassMap)
    {
        $this->nameModuleClassMap += $nameModuleClassMap;
    }

    /**
     * @return FinalizedPackageDefinition
     * @throws InvalidOperationException
     */
    public function finalize() : FinalizedPackageDefinition
    {
        if (!$this->name) {
            throw InvalidOperationException::format(
                    'Cannot finalize package definition: name has not been defined'
            );
        }

        if (!$this->nameModuleClassMap) {
            throw InvalidOperationException::format(
                    'Cannot finalize package definition: modules have not been defined'
            );
        }

        return new FinalizedPackageDefinition($this->name, $this->nameModuleClassMap, $this->dashboardWidgetNames);
    }
}