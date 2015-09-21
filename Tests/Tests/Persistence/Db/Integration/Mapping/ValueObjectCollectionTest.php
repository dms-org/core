<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\Expr;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EntityWithEmails;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EmbeddedEmailAddress;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EntityWithEmailsMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectCollectionTest extends DbIntegrationTest
{
    /**
     * @var Table
     */
    protected $entities;

    /**
     * @var Table
     */
    protected $emails;

    public function setUp()
    {
        parent::setUp();
        $this->entities = $this->db->getTable('entities')->getStructure();
        $this->emails   = $this->db->getTable('emails')->getStructure();
    }

    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return EntityWithEmailsMapper::orm();
    }

    public function testBuildsTableForEmbeddedObjectsWithForeignKey()
    {
        $this->assertEquals(
                new Table('emails', [
                        new Column('id', Integer::normal()->autoIncrement(), true),
                        new Column('entity_id', Integer::normal()),
                        new Column('email', new Varchar(255))
                ], [], [
                        new ForeignKey(
                                'fk_emails_entity_id_entities',
                                ['entity_id'],
                                'entities',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        )
                ]),
                $this->emails
        );
    }

    public function testPersist()
    {
        $entity           = new EntityWithEmails();
        $entity->emails[] = new EmbeddedEmailAddress('test@foo.com');
        $entity->emails[] = new EmbeddedEmailAddress('gmail@foo.com');
        $entity->emails[] = new EmbeddedEmailAddress('abc@foo.com');

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 1]
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 1, 'email' => 'abc@foo.com'],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entity'       => Upsert::class,
                'Insert child value objects' => Upsert::class,
        ]);
    }

    public function testPersistExisting()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1]
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 1, 'email' => 'abc@foo.com'],
                ]
        ]);

        $entity           = new EntityWithEmails(1);
        $entity->emails[] = new EmbeddedEmailAddress('what@foo.com');
        $entity->emails[] = new EmbeddedEmailAddress('a1223@boo.com');

        $this->repo->save($entity);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 1]
                ],
                'emails'   => [
                        ['id' => 4, 'entity_id' => 1, 'email' => 'what@foo.com'],
                        ['id' => 5, 'entity_id' => 1, 'email' => 'a1223@boo.com'],
                ]
        ]);

        $this->assertExecutedQueryTypes([
                'Insert parent entity'                => Upsert::class,
                'Remove previous child value objects' => Delete::class,
                'Insert child value objects'          => Upsert::class,
        ]);

        $this->assertExecutedQueryNumber(2,
                Delete::from($this->emails)
                        ->where(Expr::in(
                                Expr::tableColumn($this->emails, 'entity_id'),
                                Expr::tuple([Expr::idParam(1)])
                        ))
        );
    }

    public function testLoadEntities()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 1, 'email' => 'abc@foo.com'],
                        ['id' => 4, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        $entity           = new EntityWithEmails(1);
        $entity->emails[] = new EmbeddedEmailAddress('test@foo.com');
        $entity->emails[] = new EmbeddedEmailAddress('gmail@foo.com');
        $entity->emails[] = new EmbeddedEmailAddress('abc@foo.com');

        $entity2           = new EntityWithEmails(2);
        $entity2->emails[] = new EmbeddedEmailAddress('aaa@foo.com');

        $this->assertEquals([$entity, $entity2], $this->repo->getAll());
    }

    public function testRemove()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 1, 'email' => 'abc@foo.com'],
                        ['id' => 4, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        $this->repo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 4, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);
    }

    public function testRemoveBulk()
    {
        $this->db->setData([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 1, 'email' => 'abc@foo.com'],
                        ['id' => 4, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        $this->repo->removeAllById([1, 2]);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                ],
                'emails'   => [
                ]
        ]);
    }
}