<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Schema;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Persistence\Db\Schema\Index;

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
        $index = (new Index('index', true, ['column', 'other']))->withPrefix('foo_');


        $this->assertSame('foo_index', $index->getName());
        $this->assertSame(true, $index->isUnique());
        $this->assertSame(['foo_column', 'foo_other'], $index->getColumnNames());
    }
}