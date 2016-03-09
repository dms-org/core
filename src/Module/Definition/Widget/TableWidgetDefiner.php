<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Widget\TableWidget;

/**
 * The table widget definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableWidgetDefiner extends WidgetDefinerBase
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var ITableDisplay
     */
    private $table;

    /**
     * TableWidgetDefiner constructor.
     *
     * @param string        $name
     * @param string        $label
     * @param IAuthSystem   $authSystem
     * @param IPermission[] $requiredPermissions
     * @param ITableDisplay $table
     * @param callable      $callback
     */
    public function __construct(string $name, string $label, IAuthSystem $authSystem, array $requiredPermissions, ITableDisplay $table, callable $callback)
    {
        parent::__construct($name, $authSystem, $requiredPermissions, null, null, null, $callback);
        $this->label = $label;
        $this->table = $table;
    }

    /**
     * Defines the row criteria for the widget.
     *
     * Example:
     * <code>
     * ->matching(function (RowCriteria $criteria) {
     *      $criteria->where('column', '>', 500);
     * });
     * </code>
     *
     * @see RowCriteria
     *
     * @param callable $criteriaDefinitionCallback
     *
     * @return void
     */
    public function matching(callable $criteriaDefinitionCallback)
    {
        $criteria = $this->table->getDataSource()->criteria();
        $criteriaDefinitionCallback($criteria);

        call_user_func($this->callback, new TableWidget($this->name, $this->label, $this->authSystem, $this->requiredPermissions, $this->table, $criteria));
    }

    /**
     * Defines the table to load all rows (empty criteria).
     *
     * @return void
     */
    public function allRows()
    {
        call_user_func($this->callback, new TableWidget($this->name, $this->label, $this->authSystem, $this->requiredPermissions, $this->table));
    }
}