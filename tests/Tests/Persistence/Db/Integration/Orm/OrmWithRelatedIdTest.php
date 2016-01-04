<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\EntityRepositoryProvider;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Database;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId\ChildEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId\OneToManyIdOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId\ParentEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrmWithRelatedIdTest extends OrmTestBase
{

    /**
     * @return Orm
     */
    protected function loadOrm()
    {
        return new OneToManyIdOrm();
    }

    public function testBuildsExpectedDatabase()
    {
        $this->assertEquals(new Database([
                new Table('parents', [
                        PrimaryKeyBuilder::incrementingInt('id'),
                ]),
                new Table('children', [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('parent_id', Integer::normal()),
                        new Column('data', (new Varchar(255))->nullable()),
                ], [], [
                        new ForeignKey(
                                'fk_children_parent_id_parents',
                                ['parent_id'],
                                'parents',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                ]),
        ]), $this->orm->getDatabase());
    }

    public function testLoadRelatedObjectTypeFromProperty()
    {
        $this->assertSame(ChildEntity::class, $this->orm->loadRelatedEntityType(ParentEntity::class, 'childIds'));

        $this->assertSame(ParentEntity::class, $this->orm->loadRelatedEntityType(ChildEntity::class, 'parentId'));

        $this->assertThrows(function () {
            $this->orm->loadRelatedEntityType('SomeInvalidEntityClass', 'parentId');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () {
            $this->orm->loadRelatedEntityType(ChildEntity::class, 'someInvalidPropertyName');
        }, InvalidArgumentException::class);

        $this->assertThrows(function () {
            // Property does not map to a relation
            $this->orm->loadRelatedEntityType(ChildEntity::class, 'data');
        }, InvalidArgumentException::class);
    }

    public function testGetDataSourceProvider()
    {
        /** @var EntityRepositoryProvider $provider */
        $connection = $this->getMockForAbstractClass(IConnection::class);
        $provider = $this->orm->getEntityDataSourceProvider($connection);

        $this->assertInstanceOf(EntityRepositoryProvider::class, $provider);
        $this->assertSame($this->orm, $provider->getOrm());
        $this->assertSame($connection, $provider->getConnection());
    }
}