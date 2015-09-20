<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Orm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\ManyToManyOrm;

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
}