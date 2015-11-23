<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria;

use Iddigital\Cms\Core\Model\Criteria\MemberExpressionParser;
use Iddigital\Cms\Core\Model\Criteria\LoadCriteria;
use Iddigital\Cms\Core\Model\ILoadCriteria;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\IFinalRelationMemberMapping;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEmbeddedObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;

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
    public function newCriteria()
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
    public function mapLoadCriteriaToQuery(ILoadCriteria $criteria)
    {
        $select = $this->criteriaMapper->mapCriteriaToSelect($criteria, $memberMappings, $criteria->getAliasNestedMemberMap());
        $select->setColumns([]);

        $columnIndexMap = [];
        $relationToLoad = [];
        $requiresLoadId = false;

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

        return new MappedLoadQuery($select, $columnIndexMap, $relationToLoad);
    }
}