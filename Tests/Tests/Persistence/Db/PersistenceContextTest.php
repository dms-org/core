<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Iddigital\Cms\Core\Persistence\Db\Operations\IOperation;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Query\IQuery;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Tests\Persistence\Db\Fixtures\MockEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersistenceContextTest extends CmsTestCase
{
    public function testGetters()
    {
        $context = new PersistenceContext();

        $this->assertSame([], $context->getOperations());
    }

    public function testMarkingPersistence()
    {
        $context = new PersistenceContext();

        $entity = new MockEntity();
        $row    = $this->getMockBuilder(Row::class)->disableOriginalConstructor()->getMock();
        $this->assertFalse($context->isPersisted($entity));
        $this->assertNull($context->getPersistedRowFor($entity));

        $context->markPersisted($entity, $row);

        $this->assertTrue($context->isPersisted($entity));
        $this->assertSame($row, $context->getPersistedRowFor($entity));
        $this->assertSame($entity, $context->getPersistedEntityFor($row));

        $this->assertSame(null, $context->getPersistedRowFor(new MockEntity()));
        $this->assertSame(null, $context->getPersistedEntityFor(clone $row));
    }

    public function testQueueOperation()
    {
        $context = new PersistenceContext();

        $operation = $this->getMockForAbstractClass(IQuery::class);

        $context->queue($operation);

        $this->assertSame([$operation], $context->getOperations());
    }

    public function testCompletionsCallbacks()
    {
        $context = new PersistenceContext();

        $i = '';
        $context->afterCommit(function () use (&$i) {
            $i .= '1';
        });
        $context->afterCommit(function () use (&$i) {
            $i .= '2';
        });

        $context->fireAfterCommitCallbacks();

        $this->assertSame('12', $i, 'Must fire the completion callbacks');
    }

    public function testIgnoringRelations()
    {
        $context = new PersistenceContext();

        $relation = $this->getMockForAbstractClass(IRelation::class);
        $this->assertFalse($context->isRelationIgnored($relation));

        $called = false;
        $result = $context->ignoreRelationsFor(function () use (&$called, $context, $relation) {
            $this->assertTrue($context->isRelationIgnored($relation));
            $called = true;

            return 'abc';
        }, [$relation]);

        $this->assertTrue($called);
        $this->assertSame('abc', $result);
        $this->assertFalse($context->isRelationIgnored($relation));
    }
}