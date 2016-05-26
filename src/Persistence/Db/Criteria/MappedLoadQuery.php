<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\ParentChildMap;
use Dms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The mapped load query class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MappedLoadQuery
{
    /**
     * @var Select
     */
    protected $select;

    /**
     * @var string[]
     */
    protected $columnIndexMap;

    /**
     * @var array[]
     */
    protected $relationsToLoad;

    /**
     * MappedLoadQuery constructor.
     *
     * @param Select           $select
     * @param string[]         $columnIndexMap
     * @param array[] $relationsToLoad
     */
    public function __construct(Select $select, array $columnIndexMap, array $relationsToLoad)
    {
        $this->select          = $select;
        $this->columnIndexMap  = $columnIndexMap;
        $this->relationsToLoad = $relationsToLoad;
    }

    /**
     * @return Select
     */
    public function getSelect() : Select
    {
        return $this->select;
    }

    /**
     * @param LoadingContext $context
     *
     * @return array[]
     */
    public function load(LoadingContext $context) : array
    {
        $rows      = $context->query($this->select)->getRows();
        $data      = [];

        foreach ($this->columnIndexMap as $columnName => $index) {
            foreach ($rows as $key => $row) {
                $data[$key][$index] = $row->getColumn($columnName);
            }
        }

        foreach ($this->relationsToLoad as $index => list($relation, $parentTable, $parentColumnMap)) {
            /** @var Table $parentTable */
            if ($relation instanceof IToOneRelation) {
                $map       = new ParentChildMap($parentTable->getPrimaryKeyColumnName());
                $rowKeyMap = new \SplObjectStorage();

                foreach ($rows as  $key => $row) {
                    $mappedColumnData = [];
                    $originalColumnData = $row->getColumnData();

                    foreach($parentColumnMap as $original => $mapped) {
                        $mappedColumnData[$mapped] = $originalColumnData[$original];
                    }

                    $mappedRow = new Row($parentTable, $mappedColumnData);

                    $map->add($mappedRow, null);
                    $rowKeyMap[$mappedRow] = $key;
                }

                $relation->load($context, $map);

                foreach ($map->getItems() as $item) {
                    $data[$rowKeyMap[$item->getParent()]][$index] = $item->getChild();
                }
            } elseif ($relation instanceof IToManyRelation) {
                $map = new ParentChildrenMap($parentTable->getPrimaryKeyColumnName());
                $rowKeyMap = new \SplObjectStorage();

                foreach ($rows as $key => $row) {
                    $mappedColumnData = [];
                    $originalColumnData = $row->getColumnData();

                    foreach($parentColumnMap as $original => $mapped) {
                        $mappedColumnData[$mapped] = $originalColumnData[$original];
                    }

                    $mappedRow = new Row($parentTable, $mappedColumnData);

                    $map->add($mappedRow, []);
                    $rowKeyMap[$mappedRow] = $key;
                }

                $relation->load($context, $map);

                foreach ($map->getItems() as $item) {
                    $data[$rowKeyMap[$item->getParent()]][$index] = $relation->buildCollection($item->getChildren());
                }
            }
        }

        return $data;
    }
}