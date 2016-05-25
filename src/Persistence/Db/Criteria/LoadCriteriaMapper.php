<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\IFinalRelationMemberMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEmbeddedObjectMapping;
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
        $primaryKeyColumnName = $this->criteriaMapper->getMapper()->getPrimaryTable()->getPrimaryKeyColumnName();

        $columnIndexMap  = [];
        $relationsToLoad = [];
        $requiresLoadId  = false;

        foreach ($criteria->getAliasNestedMemberMap() as $alias => $member) {
            /** @var MemberMappingWithTableAliases $memberMapping */
            $memberMapping = $memberMappings[$member->asString()];
            $mapping       = $memberMapping->getMapping();

            if ($mapping instanceof IFinalRelationMemberMapping) {
                $memberRelation   = $mapping->asMemberRelation();
                $parentTableAlias = !($memberRelation->getFirstRelation() instanceof ISeparateTableRelation) || count($memberMapping->getTableAliases()) === 1
                    ? $memberMapping->getLastTableAlias()
                    : $memberMapping->getSecondLastTableAlias();
                $parentTable      = $select->getTableFromAlias($parentTableAlias);
                $parentColumnMap  = [];

                foreach ($memberRelation->getParentColumnsToLoad() as $column) {
                    $parentColumnMap[$alias . '_' . $column] = $column;

                    $select->addColumn($alias . '_' . $column, Expr::column($parentTableAlias, $parentTable->getColumn($column)));
                }

                if (!($mapping instanceof ToOneEmbeddedObjectMapping)) {
                    $requiresLoadId = true;
                    $parentColumnMap[$primaryKeyColumnName] = $primaryKeyColumnName;
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

        if ($requiresLoadId) {
            $select->addRawColumn($primaryKeyColumnName);
        }

        return new MappedLoadQuery($select, $columnIndexMap, $relationsToLoad);
    }
}