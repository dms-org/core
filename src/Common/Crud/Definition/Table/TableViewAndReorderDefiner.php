<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Table;

use Dms\Core\Auth\IPermission;
use Dms\Core\Module\Definition\Table\TableViewDefiner;
use Dms\Core\Table\ITableDataSource;

/**
 * The table view and reorder action definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewAndReorderDefiner extends TableViewDefiner
{
    /**
     * @var callable
     */
    protected $reorderActionCallback;

    /**
     * @inheritDoc
     */
    public function __construct(ITableDataSource $dataSource, $name, $label, callable $reorderActionCallback)
    {
        parent::__construct($dataSource, $name, $label);
        $this->reorderActionCallback = $reorderActionCallback;
    }

    /**
     * Defines that, when viewed with the this criteria, the table
     * rows can be reordered within their sections. The supplied callback
     * will be run when a row is reordered.
     *
     * Extra permissions to perform this reorder can be passed as the second
     * parameter. The {@see IReadModule::VIEW_PERMISSION} and {@see ICrudModule::EDIT_PERMISSION}
     * will be automatically applied to this action.
     *
     * The action name will default to the format "summary-table.{view-name}.reorder".
     *
     * Example:
     * <code>
     * ->withReorder(function (Person $person, $newIndex) {
     *      $this->dataSource->reorderPersonInGroup($person, $newIndex);
     * });
     * </code>
     *
     * @param callable      $callback
     * @param IPermission[] $permissions
     * @param string|null   $actionName
     *
     * @return static
     */
    public function withReorder(callable $callback, array $permissions = [], string $actionName = null)
    {
        call_user_func($this->reorderActionCallback, $callback, $permissions, $actionName);

        return $this;
    }
}