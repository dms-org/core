<?php

namespace Dms\Core\Tests\Persistence\Db\Schema;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;

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
        $this->assertSame(true, $foreignKey->requiresNullableColumns());
        $this->assertSame(ForeignKeyMode::CASCADE, $foreignKey->getOnDeleteMode());
        $this->assertSame(ForeignKeyMode::SET_NULL, $foreignKey->getOnUpdateMode());
    }

    public function testMismatchedColumns()
    {
        $this->expectException(InvalidArgumentException::class);

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
        $this->expectException(InvalidArgumentException::class);

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
        $this->expectException(InvalidArgumentException::class);

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
        $this->assertSame(true, $foreignKey->requiresNullableColumns());
        $this->assertSame(ForeignKeyMode::CASCADE, $foreignKey->getOnDeleteMode());
        $this->assertSame(ForeignKeyMode::SET_NULL, $foreignKey->getOnUpdateMode());
    }


    public function testNoColumns()
    {
        $this->expectException(InvalidArgumentException::class);

        new ForeignKey(
                'fk',
                [],
                'other_table',
                [],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::CASCADE
        );
    }

    public function testWithNamingConvention()
    {
        $fk = ForeignKey::createWithNamingConvention(
                'parent_table',
                ['some_id', 'other_id'],
                'other_table',
                ['referenced_id', 'other_referenced_id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::CASCADE
        );

        $this->assertSame('fk_parent_table_some_id_other_id_other_table', $fk->getName());
        $this->assertSame(false, $fk->requiresNullableColumns());
    }

    public function testWithPrefixKeepsFkConventionPrefix()
    {
        $fk = ForeignKey::createWithNamingConvention(
                'parent_table',
                ['some_id'],
                'other_table',
                ['referenced_id'],
                ForeignKeyMode::CASCADE,
                ForeignKeyMode::CASCADE
        )->withPrefix('foo_');

        $this->assertSame('fk_foo_parent_table_some_id_other_table', $fk->getName());
        $this->assertSame(false, $fk->requiresNullableColumns());
    }
}