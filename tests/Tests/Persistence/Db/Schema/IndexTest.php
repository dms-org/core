<?php

namespace Dms\Core\Tests\Persistence\Db\Schema;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Index;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndexTest extends CmsTestCase
{
    public function testGetters()
    {
        $index = new Index('index', true, ['column']);

        $this->assertSame('index', $index->getName());
        $this->assertSame(true, $index->isUnique());
        $this->assertSame(['column'], $index->getColumnNames());
    }

    public function testWithPrefix()
    {
        $index = (new Index('index', false, ['column', 'other']))->withPrefix('foo_');


        $this->assertSame('foo_index', $index->getName());
        $this->assertSame(false, $index->isUnique());
        $this->assertSame(['foo_column', 'foo_other'], $index->getColumnNames());
    }

    public function testNoColumns()
    {
        $this->expectException(InvalidArgumentException::class);

        new Index('foo', false, []);
    }
}