<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Schema;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnTest extends CmsTestCase
{
    public function testGetters()
    {
        $column = new Column('data', $type = new Varchar(255));

        $this->assertSame('data', $column->getName());
        $this->assertSame($type, $column->getType());
        $this->assertFalse($column->isPrimaryKey());
    }
}