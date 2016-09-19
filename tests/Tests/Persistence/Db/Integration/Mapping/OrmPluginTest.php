<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\Plugin\IOrmPlugin;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Id\EmptyMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrmPluginTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([EmptyEntity::class => EmptyMapper::class])
            ->update('', [$this->ormPlugin()]);
    }

    protected function ormPlugin()
    {
        return new class () implements IOrmPlugin
        {

            /**
             * Hook for the defining an object mapper.
             *
             * @param IObjectMapper    $mapper
             * @param MapperDefinition $map
             *
             * @return void
             */
            public function defineMapper(IObjectMapper $mapper, MapperDefinition $map)
            {
                $map->computed(function () {
                    return 10;
                })->to('data')->asInt();
            }

            /**
             * Hook for loading the SELECT query from the supplied object mapper.
             *
             * @param IObjectMapper $mapper
             * @param Select        $select
             *
             * @return void
             */
            public function loadSelect(IObjectMapper $mapper, Select $select)
            {
                $select->where(Expr::equal(
                    Expr::tableColumn($select->getTable(), 'data'),
                    Expr::param(null, 10)
                ));
            }
        };
    }

    public function testPersist()
    {
        $this->repo->save(new EmptyEntity());
        $this->repo->save(new EmptyEntity());

        $this->assertDatabaseDataSameAs([
            'data' => [
                ['id' => 1, 'data' => 10],
                ['id' => 2, 'data' => 10],
            ],
        ]);
    }

    public function testLoad()
    {
        $this->setDataInDb([
            'data' => [
                ['id' => 1, 'data' => 10],
                ['id' => 2, 'data' => 100],
                ['id' => 3, 'data' => 10],
            ],
        ]);

        $this->assertEquals([
            new EmptyEntity(1),
            new EmptyEntity(3),
        ], $this->repo->getAll());

        $this->assertExecutedQueries([
            Select::allFrom($this->table->getStructure())
                ->where(Expr::equal(
                    Expr::tableColumn($this->table->getStructure(), 'data'),
                    Expr::param(null, 10)
                )),
        ]);
    }
}