<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition\Table;

use Iddigital\Cms\Core\Module\Definition\Table\TableViewDefiner;
use Iddigital\Cms\Core\Table\ITableDataSource;

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
     * Example:
     * <code>
     * ->withReorder(function (Person, $newIndex) {
     *      $this->repository->reorderPersonInGroup($person, $newIndex);
     * });
     * </code>
     *
     * @param callable $callback
     *
     * @return static
     */
    public function withReorder(callable $callback)
    {
        call_user_func($this->reorderActionCallback, $callback);

        return $this;
    }
}