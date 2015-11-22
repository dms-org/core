<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityRepositoryProvider;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Orm;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Database;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId\ChildEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId\OneToManyIdOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\RelatedId\ParentEntity;

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
                        new Column('id', Integer::normal()->autoIncrement(), true),
                ]),
                new Table('children', [
                        new Column('id', Integer::normal()->autoIncrement(), true),
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