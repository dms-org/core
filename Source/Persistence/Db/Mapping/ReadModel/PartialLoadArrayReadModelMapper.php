<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IPartialLoadCriteria;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\IFinalRelationMemberMapping;
use Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping\MemberMapping;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Row;

/**
 * The partial load array read model mapper class.
 *
 * This can load read models in the form of arrays.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PartialLoadArrayReadModelMapper extends ReadModelMapper
{
    /**
     * ArrayReadModelMapper constructor.
     *
     * @param IObjectMapper $fromMapper
     * @param IPartialLoadCriteria $criteria
     * @param MemberMapping[] $memberMappings
     *
     */
    public function __construct(IObjectMapper $fromMapper, IPartialLoadCriteria $criteria, array $memberMappings)
    {
        $definition = new ReadMapperDefinition($fromMapper->getDefinition()->getOrm());
        $definition->from($fromMapper);
        $this->loadDefinitionFromMemberMappings($definition, $criteria, $memberMappings);

        parent::__construct($definition);
        $this->initializeRelations();
    }

    /**
     * @param LoadingContext $context
     * @param Row[]          $rows
     *
     * @return array[]
     */
    public function loadAllAsArray(LoadingContext $context, array $rows)
    {
        /** @var ArrayReadModel[] $models */
        $models = $this->loadAll($context, $rows);

        foreach ($models as $key => $model) {
            $models[$key] = $model->data + $rows[$key]->getColumnData();
        }

        return $models;
    }

    /**
     * @param ReadMapperDefinition $definition
     * @param IPartialLoadCriteria $criteria
     * @param MemberMapping[]      $memberMappings
     *
     * @throws InvalidArgumentException
     */
    protected function loadDefinitionFromMemberMappings(
            ReadMapperDefinition $definition,
            IPartialLoadCriteria $criteria,
            array $memberMappings
    ) {
        $definition->type(ArrayReadModel::class);

        foreach ($criteria->getAliasNestedMemberMap() as $alias => $member) {
            $mapping = $memberMappings[$member->asString()];

            if ($mapping instanceof IFinalRelationMemberMapping) {
                $emptyFunction = function () {
                };
                $definition->getReadDefinition()
                        ->accessorRelation($emptyFunction, function (ArrayReadModel $readModel, $value) use ($alias) {
                            $readModel->data[$alias] = $value;
                        })
                        ->asCustom($mapping->asMemberRelation());
            }
        }
    }
}