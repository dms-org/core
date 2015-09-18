<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Schema;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyTest extends CmsTestCase
{
    public function testNew()
    {
        $foreignKey = new ForeignKey(
                'fk',
                ['some_id'],
                'other_table',
                ['referenced_id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::SET_NULL
        );

        $this->assertSame('fk', $foreignKey->getName());
        $this->assertSame(['some_id'], $foreignKey->getLocalColumnNames());
        $this->assertSame('other_table', $foreignKey->getReferencedTableName());
        $this->assertSame(['referenced_id'], $foreignKey->getReferencedColumnNames());
        $this->assertSame(ForeignKeyMode::CASCADE, $foreignKey->getOnDeleteMode());
        $this->assertSame(ForeignKeyMode::SET_NULL, $foreignKey->getOnUpdateMode());
    }

    public function testMismatchedColumns()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new ForeignKey(
                'fk',
                ['some_id'],
                'other_table',
                ['referenced_id', 'another_id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::SET_NULL
        );
    }

    public function testInvalidDeleteMode()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new ForeignKey(
                'fk',
                ['some_id'],
                'other_table',
                ['referenced_id'],
                'foobar',
                ForeignKeyMode::SET_NULL
        );
    }

    public function testInvalidUpdateMode()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new ForeignKey(
                'fk',
                ['some_id'],
                'other_table',
                ['referenced_id'],
                ForeignKeyMode::CASCADE,
                'foobar'
        );
    }

    public function testWithPrefix()
    {
        $foreignKey = (new ForeignKey(
                'fk',
                ['some_id'],
                'other_table',
                ['referenced_id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::SET_NULL
        ))->withPrefix('foo_');

        $this->assertSame('foo_fk', $foreignKey->getName());
        $this->assertSame(['foo_some_id'], $foreignKey->getLocalColumnNames());
        $this->assertSame('other_table', $foreignKey->getReferencedTableName());
        $this->assertSame(['referenced_id'], $foreignKey->getReferencedColumnNames());
        $this->assertSame(ForeignKeyMode::CASCADE, $foreignKey->getOnDeleteMode());
        $this->assertSame(ForeignKeyMode::SET_NULL, $foreignKey->getOnUpdateMode());
    }

}