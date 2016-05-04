<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany\ParentEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ToOneThanManyTest extends DbIntegrationTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return ParentEntityMapper::orm();
    }

    public function testLoadCriteria()
    {
        $this->setDataInDb([
            'parents'  => [
                ['id' => 1],
            ],
            'subs'     => [
                ['id' => 1, 'parent_id' => 1],
            ],
            'children' => [
                ['id' => 1, 'sub_id' => 1],
                ['id' => 2, 'sub_id' => 1],
                ['id' => 3, 'sub_id' => 1],
            ],
        ]);

        $data = $this->repo->loadMatching(
            $this->repo->loadCriteria()
                ->loadAll([
                    'load(subEntityId)'                   => 'sub',
                    'load(subEntityId).loadAll(childIds)' => 'to-many',
                ])
        );

        $this->assertEquals([
            [
                'sub'     => new SubEntity(1, [1, 2, 3]),
                'to-many' => ChildEntity::collection([
                    new ChildEntity(1),
                    new ChildEntity(2),
                    new ChildEntity(3),
                ]),
            ],
        ], $data);
    }
}