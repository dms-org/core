<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\IFinalRelationMemberMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEmbeddedObjectMapping;
use Dms\Core\Persistence\Db\Query\Expression\Expr;

/**
 * The partial load criteria mapper class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadCriteriaMapper
{
    /**
     * @var CriteriaMapper
     */
    private $criteriaMapper;

    /**
     * LoadCriteriaMapper constructor.
     *
     * @param CriteriaMapper $criteriaMapper
     */
    public function __construct(CriteriaMapper $criteriaMapper)
    {
        $this->criteriaMapper = $criteriaMapper;
    }

    /**
     * @return LoadCriteria
     */
    public function newCriteria() : LoadCriteria
    {
        $memberExpressionParser = $this->criteriaMapper->buildMemberExpressionParser();

        return new LoadCriteria($this->criteriaMapper->getMappedObjectType(), $memberExpressionParser);
    }

    /**
     * Maps the supplied criteria to a select query.
     *
     * @param ILoadCriteria $criteria
     *
     * @return MappedLoadQuery
     */
    public function mapLoadCriteriaToQuery(ILoadCriteria $criteria) : MappedLoadQuery
    {
        $select = $this->criteriaMapper->mapCriteriaToSelect($criteria, $memberMappings, $criteria->getAliasNestedMemberMap());
        $select->setColumns([]);

        $columnIndexMap  = [];
        $relationsToLoad = [];
        $requiresLoadId  = false;

        foreach ($criteria->getAliasNestedMemberMap() as $alias => $member) {
            /** @var MemberMappingWithTableAlias $memberMapping */
            $memberMapping = $memberMappings[$member->asString()];
            $mapping       = $memberMapping->getMapping();

            if ($mapping instanceof IFinalRelationMemberMapping) {
                $memberRelation          = $mapping->asMemberRelation();
                $relationsToLoad[$alias] = $memberRelation;

                foreach ($memberRelation->getParentColumnsToLoad() as $column) {
                    if ($select->getTable()->hasColumn($column)) {
                        $select->addColumn($column, Expr::tableColumn($select->getTable(), $column));
                        continue;
                    } else {
                        foreach ($select->getJoins() as $join) {
                            if ($join->getTable()->hasColumn($column)) {
                                $select->addColumn($column, Expr::column($join->getTableName(), $join->getTable()->getColumn($column)));
                                continue;
                            }
                        }
                    }
                }

                if (!($mapping instanceof ToOneEmbeddedObjectMapping)) {
                    $requiresLoadId = true;
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

        if ($requiresLoadId) {
            $select->addRawColumn($this->criteriaMapper->getMapper()->getPrimaryTable()->getPrimaryKeyColumnName());
        }

        return new MappedLoadQuery($select, $columnIndexMap, $relationsToLoad);
    }
}