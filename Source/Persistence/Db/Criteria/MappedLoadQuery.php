<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ParentChildrenMap;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Relation\MemberRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IToOneRelation;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;

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
     * @var MemberRelation[]
     */
    protected $relationsToLoad;

    /**
     * MappedLoadQuery constructor.
     *
     * @param Select           $select
     * @param string[]         $columnIndexMap
     * @param MemberRelation[] $relationsToLoad
     */
    public function __construct(Select $select, array $columnIndexMap, array $relationsToLoad)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'relationsToLoad', $relationsToLoad, MemberRelation::class);
        $this->select          = $select;
        $this->columnIndexMap  = $columnIndexMap;
        $this->relationsToLoad = $relationsToLoad;
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param LoadingContext $context
     *
     * @return array[]
     */
    public function load(LoadingContext $context)
    {
        $rows      = $context->query($this->select)->getRows();
        $rowKeyMap = new \SplObjectStorage();
        $data      = [];

        foreach ($rows as $key => $row) {
            $rowKeyMap[$row] = $key;
        }

        foreach ($this->columnIndexMap as $columnName => $index) {
            foreach ($rows as $key => $row) {
                $data[$key][$index] = $row->getColumn($columnName);
            }
        }

        $primaryKey = $this->select->getTable()->getPrimaryKeyColumnName();

        foreach ($this->relationsToLoad as $index => $relation) {
            if ($relation instanceof IToOneRelation) {
                $map = new ParentChildMap($primaryKey);

                foreach ($rows as $row) {
                    $map->add($row, null);
                }

                $relation->load($context, $map);

                foreach ($map->getItems() as $item) {
                    $data[$rowKeyMap[$item->getParent()]][$index] = $item->getChild();
                }
            } elseif ($relation instanceof IToManyRelation) {
                $map = new ParentChildrenMap($primaryKey);

                foreach ($rows as $row) {
                    $map->add($row, []);
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