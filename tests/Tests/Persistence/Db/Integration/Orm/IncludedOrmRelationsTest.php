<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\ManyToManyOrm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IncludedOrmRelationsTest extends OrmRelationsTest
{
    /**
     * @return Orm
     */
    protected function loadOrm()
    {
        return new CustomOrm(function (OrmDefinition $orm) {
            $orm->encompass(new ManyToManyOrm());
        });
    }

    public function testGetRootOrm()
    {
        $this->assertSame($this->orm, $this->orm->getRootOrm());
        $this->assertSame($this->orm, $this->orm->getIncludedOrms()[0]->getRootOrm());
    }
}