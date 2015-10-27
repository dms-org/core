<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Hook;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Hook\OrderIndexPropertyLoaderHook;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The hook type definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class HookTypeDefiner
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * HookTypeDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Defines hook which saves sequential order index numbers (1-based)
     * to the supplied column.
     *
     * If the grouping column is supplied, the order index numbers will be
     * sequential within its group.
     *
     * @param string      $columnName
     * @param string|null $groupingColumnName
     *
     * @return void
     */
    public function saveOrderIndexTo($columnName, $groupingColumnName = null)
    {
        call_user_func($this->callback, function (Table $table, array $propertyColumnMap) use ($columnName, $groupingColumnName) {
            return new OrderIndexPropertyLoaderHook(
                    $table,
                    $columnName,
                    $groupingColumnName,
                    array_search($columnName, $propertyColumnMap, true) ?: null
            );
        });
    }
}