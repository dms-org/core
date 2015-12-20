<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Constraints\ConstrainedEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Constraints\ConstraintsEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConstraintsEntityMapperTest extends DbIntegrationTest
{
    public function setUp()
    {
        $this->mapper = $this->loadOrm()->getEntityMapper(ConstrainedEntity::class);
    }

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([ConstrainedEntity::class => ConstraintsEntityMapper::class]);
    }

    public function testLoadsCorrectIndexes()
    {
        $this->assertEquals(
                [
                        'index_name'        => new Index('index_name', false, ['indexed']),
                        'unique_index_name' => new Index('unique_index_name', true, ['unique']),
                ],
                $this->mapper->getPrimaryTable()->getIndexes()
        );
    }

    public function testLoadsCorrectForeignKey()
    {
        $this->assertEquals(
                [
                        'fk_name' => new ForeignKey(
                                'fk_name',
                                ['fk'],
                                'some_other_table',
                                ['some_other_id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                ],
                $this->mapper->getPrimaryTable()->getForeignKeys()
        );
    }
}