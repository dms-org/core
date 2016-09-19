<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria\Load;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\MappedLoadQuery;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToManyRelationMapping;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToManyMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ToOneRelation;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany\ParentEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadCriteriaWithToOneThenToManyRelation extends LoadCriteriaMapperTestBase
{
    protected function buildMapper()
    {
        return new CriteriaMapper(
            ParentEntityMapper::orm()->getEntityMapper(ParentEntity::class),
            $this->getMockForAbstractClass(IConnection::class)
        );
    }

    public function testLoadToManyRelation()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll([
                'load(subEntityId).loadAll(childIds)' => 'to-many',
            ]);

        $objectMapper = $this->mapper->getMapper();
        /** @var ToOneRelation $subEntityRelation */
        /** @var ToManyRelation $childRelation */
        $subEntityRelation = $objectMapper->getDefinition()->getRelationMappedToProperty('subEntityId');
        $childRelation     = $subEntityRelation->getEntityMapper()->getDefinition()->getRelationMappedToProperty('childIds');

        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()->addRawColumn('id'),
            [], [
                'to-many' => new ToManyMemberRelation(
                    (new ToManyRelationMapping($objectMapper, [], [$subEntityRelation->withObjectReference()], $childRelation->withObjectReference()))
                ),
            ]
        ));
    }
}