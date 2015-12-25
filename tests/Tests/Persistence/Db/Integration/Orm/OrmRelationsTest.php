<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Database;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\ManyToManyOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrmRelationsTest extends OrmTestBase
{

    /**
     * @return Orm
     */
    protected function loadOrm()
    {
        return new ManyToManyOrm();
    }

    public function testBuildsExpectedDatabase($namespace = '')
    {
        $this->assertEquals(new Database([
                new Table($namespace . 'ones', [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                ]),
                new Table($namespace . 'anothers', [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                ]),
                new Table($namespace . 'one_anothers', [
                        new Column('one_id', Integer::normal()),
                        new Column('another_id', Integer::normal()),
                ], [], [
                        new ForeignKey(
                                "fk_{$namespace}one_anothers_one_id_{$namespace}ones",
                                ['one_id'],
                                $namespace . 'ones',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                        new ForeignKey(
                                "fk_{$namespace}one_anothers_another_id_{$namespace}anothers",
                                ['another_id'],
                                $namespace . 'anothers',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                ]),
        ]), $this->orm->inNamespace($namespace)->getDatabase());
    }

    public function testStructureWithNamespace()
    {
        $this->testBuildsExpectedDatabase('namespace_');
    }

    public function testLoadingEntityMapper()
    {
        $this->assertSame(true, $this->orm->hasEntityMapper(OneEntity::class));
        $this->assertSame(true, $this->orm->hasEntityMapper(OneEntity::class, 'ones'));

        $this->assertSame(false, $this->orm->hasEntityMapper(OneEntity::class, 'non_existent'));
        $this->assertSame(false, $this->orm->hasEntityMapper(\stdClass::class));

        $this->assertInstanceOf(OneEntityMapper::class, $this->orm->getEntityMapper(OneEntity::class));
        $this->assertInstanceOf(OneEntityMapper::class, $this->orm->getEntityMapper(OneEntity::class, 'ones'));
        $this->assertThrows(function () {
            $this->orm->getEntityMapper(OneEntity::class, 'non_existent');
        }, InvalidArgumentException::class);
        $this->assertThrows(function () {
            $this->orm->getEntityMapper(\stdClass::class);
        }, InvalidArgumentException::class);

        $this->assertInstanceOf(AnotherEntityMapper::class, $this->orm->getEntityMapper(AnotherEntity::class));
        $this->assertInstanceOf(AnotherEntityMapper::class, $this->orm->getEntityMapper(AnotherEntity::class, 'anothers'));
        $this->assertThrows(function () {
            $this->orm->getEntityMapper(AnotherEntityMapper::class, 'non_existent');
        }, InvalidArgumentException::class);

        $this->assertInstanceOf(OneEntityMapper::class, $this->orm->findEntityMapper(OneEntity::class));
        $this->assertInstanceOf(OneEntityMapper::class, $this->orm->findEntityMapper(OneEntity::class, 'ones'));
        $this->assertSame(null, $this->orm->findEntityMapper(\stdClass::class));
    }
}