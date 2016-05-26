<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\IFinalRelationMemberMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEmbeddedObjectMapping;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToManyMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ISeparateTableRelation;
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

        foreach ($criteria->getAliasNestedMemberMap() as $alias => $member) {
            /** @var MemberMappingWithTableAliases $memberMapping */
            $memberMapping = $memberMappings[$member->asString()];
            $mapping       = $memberMapping->getMapping();

            if ($mapping instanceof IFinalRelationMemberMapping) {
                $memberRelation   = $mapping->asMemberRelation();
                $parentTableAlias = $memberRelation instanceof ToManyMemberRelation || !($memberRelation->getFirstRelation() instanceof ISeparateTableRelation) || count($memberMapping->getTableAliases()) === 1
                    ? $memberMapping->getLastTableAlias()
                    : $memberMapping->getSecondLastTableAlias();
                $parentTable      = $select->getTableFromAlias($parentTableAlias);
                $parentColumnMap  = [];

                $parentColumnsToLoad = $memberRelation->getParentColumnsToLoad();

                if (!($mapping instanceof ToOneEmbeddedObjectMapping)) {
                    $parentColumnsToLoad[] = $parentTable->getPrimaryKeyColumnName();
                }

                foreach ($parentColumnsToLoad as $column) {
                    $parentColumnMap[$alias . '_' . $column] = $column;

                    $select->addColumn($alias . '_' . $column, Expr::column($parentTableAlias, $parentTable->getColumn($column)));
                }

                $relationsToLoad[$alias] = [$memberRelation, $parentTable, $parentColumnMap];
            } else {
                $mapping->addSelectColumn($select, $memberMapping->getLastTableAlias(), $alias);
                $columnIndexMap[$alias] = $alias;
            }
        }

        foreach ($memberMappings as $key => $memberMapping) {
            /** @var MemberMappingWithTableAliases $memberMapping */
            $memberMappings[$key] = $memberMapping->getMapping();
        }


        return new MappedLoadQuery($select, $columnIndexMap, $relationsToLoad);
    }
}