<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria;

use Iddigital\Cms\Core\Model\IPartialLoadCriteria;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\IFinalRelationMemberMapping;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;

/**
 * The partial load criteria mapper class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PartialLoadCriteriaMapper
{
    /**
     * @var CriteriaMapper
     */
    private $criteriaMapper;

    /**
     * PartialLoadCriteriaMapper constructor.
     *
     * @param CriteriaMapper $criteriaMapper
     */
    public function __construct(CriteriaMapper $criteriaMapper)
    {
        $this->criteriaMapper = $criteriaMapper;
    }

    /**
     * Maps the supplied criteria to a select query.
     *
     * @param IPartialLoadCriteria $criteria
     *
     * @return MappedPartialLoadQuery
     */
    public function mapPartialLoadCriteriaToQuery(IPartialLoadCriteria $criteria)
    {
        $select = $this->criteriaMapper->mapCriteriaToSelect($criteria, $memberMappings);
        $select->setColumns([]);
        $select->addRawColumn($this->criteriaMapper->getMapper()->getPrimaryTable()->getPrimaryKeyColumnName());

        $columnIndexMap = [];
        $relationToLoad = [];

        foreach ($criteria->getAliasNestedMemberMap() as $alias => $member) {
            /** @var MemberMappingWithTableAlias $memberMapping */
            $memberMapping = $memberMappings[$member->asString()];
            $mapping       = $memberMapping->getMapping();

            if ($mapping instanceof IFinalRelationMemberMapping) {
                $memberRelation         = $mapping->asMemberRelation();
                $relationToLoad[$alias] = $memberRelation;

                foreach ($memberRelation->getParentColumnsToLoad() as $column) {
                    $select->addColumn($column, Expr::tableColumn($select->getTable(), $column));
                }
            } else {
                $mapping->addSelectColumn($select, $memberMapping->getTableAlias(), $alias);
                $columnIndexMap[$alias] = $alias;
            }
        }

        foreach ($memberMappings as $key => $memberMapping) {
            /** @var MemberMappingWithTableAlias $memberMapping */
            $memberMappings[$key] = $memberMapping->getMapping();
        }

        return new MappedPartialLoadQuery($select, $columnIndexMap, $relationToLoad);
    }
}