<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Hook;

use Dms\Core\Persistence\Db\Mapping\Hook\OrderIndexPropertyLoaderHook;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Schema\Table;

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
    public function saveOrderIndexTo(string $columnName, string $groupingColumnName = null)
    {
        call_user_func($this->callback, function (Table $table, $uniqueKey, array $propertyColumnMap, $objectType) use ($columnName, $groupingColumnName) {
            return new OrderIndexPropertyLoaderHook(
                    $objectType,
                    $table,
                    $columnName,
                    $groupingColumnName,
                    array_search($columnName, $propertyColumnMap, true) ?: null
            );
        });
    }
}