<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping;

use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Persistence\Db\Mapping\Relation\Lazy\Collection\LazyValueObjectCollection;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Upsert;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\PrimaryKeyBuilder;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EmbeddedEmailAddress;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EntityWithEmails;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EntityWithEmailsMapper;

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

    public function setUp(): void
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

    public function testForeignKeyIsCompatibleWithReferencedColumn()
    {
        $this->assertEquals(
                $this->entities->getPrimaryKeyColumn()->getType(),
                $this->emails->getColumn('entity_id')->getType()->autoIncrement()
        );
    }

    public function testBuildsTableForEmbeddedObjectsWithForeignKey()
    {
        $this->assertEquals(
                new Table('emails', [
                        PrimaryKeyBuilder::incrementingInt('id'),
                        new Column('entity_id', PrimaryKeyBuilder::primaryKeyType()),
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
        $this->setDataInDb([
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
        $this->setDataInDb([
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
        $this->setDataInDb([
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
        $this->setDataInDb([
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

    public function testLoadPartial()
    {
        $this->setDataInDb([
                'entities' => [
                        ['id' => 1],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                ]
        ]);

        $this->assertEquals(
                [
                        [
                                'emails' => EmbeddedEmailAddress::collection([
                                        new EmbeddedEmailAddress('test@foo.com'),
                                        new EmbeddedEmailAddress('gmail@foo.com'),
                                ]),
                        ]
                ],
                $this->repo->loadMatching(
                        $this->repo->loadCriteria()
                                ->loadAll(['emails'])
                )
        );
    }

    public function testCriteriaWithCountAggregate()
    {
        $this->setDataInDb([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        $this->assertEquals(
                [
                        new EntityWithEmails(
                                1,
                                [new EmbeddedEmailAddress('test@foo.com'), new EmbeddedEmailAddress('gmail@foo.com'),]
                        ),
                ],
                $this->repo->matching(
                        $this->repo->criteria()
                                ->where('emails.count()', '>=', 2)
                )
        );
    }

    public function testWhereHasAny()
    {
        $this->setDataInDb([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        $this->assertEquals(
                [
                        new EntityWithEmails(
                                1,
                                [new EmbeddedEmailAddress('test@foo.com'), new EmbeddedEmailAddress('gmail@foo.com'),]
                        )
                ],
                $this->repo->matching(
                        $this->repo->criteria()
                                ->whereHasAny('emails', EmbeddedEmailAddress::specification(function (SpecificationDefinition $match) {
                                    $match->whereStringContains('email', 'gmail');
                                }))
                )
        );
    }

    public function testLoadingInLazyMode()
    {
        $this->orm->enableLazyLoading();

        $this->setDataInDb([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        /** @var EntityWithEmails[] $entities */
        $entities = $this->repo->getAll();

        foreach ($entities as $entity) {
            /** @var LazyValueObjectCollection $collection */
            $collection = $entity->emails;

            $this->assertInstanceOf(LazyValueObjectCollection::class, $collection);
            $this->assertSame(false, $collection->hasLoadedElements());
        }

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class
        ]);

        $firstEmails  = $entities[0]->emails->asArray();
        $secondEmails = $entities[1]->emails->asArray();

        $this->assertExecutedQueryTypes([
                'Load parent entities'             => Select::class,
                'Load *all* related value objects' => Select::class,
        ]);

        $this->assertEquals([new EmbeddedEmailAddress('test@foo.com'), new EmbeddedEmailAddress('gmail@foo.com')], $firstEmails);
        $this->assertEquals([new EmbeddedEmailAddress('aaa@foo.com')], $secondEmails);
    }

    public function testSaveLazyLoadedCollectionDoesNotPerformSyncQueries()
    {
        $this->orm->enableLazyLoading();

        $this->setDataInDb([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);

        /** @var EntityWithEmails[] $entities */
        $entities = $this->repo->getAll();

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class
        ]);

        $this->repo->saveAll($entities);

        $this->assertExecutedQueryTypes([
                'Load parent entities' => Select::class,
                'Save parent entities' => Upsert::class,
        ]);

        $this->assertDatabaseDataSameAs([
                'entities' => [
                        ['id' => 1],
                        ['id' => 2],
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 2, 'email' => 'aaa@foo.com'],
                ]
        ]);
    }
}