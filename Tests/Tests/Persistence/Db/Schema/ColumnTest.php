<?php

namespace Dms\Core\Tests\Persistence\Db\Schema;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

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