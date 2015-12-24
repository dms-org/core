<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomOrmRelationsTest extends OrmRelationsTest
{

    /**
     * @return Orm
     */
    protected function loadOrm()
    {
        return new CustomOrm(function (OrmDefinition $orm) {
            $orm->entity(OneEntity::class)->from(OneEntityMapper::class);

            $orm->entity(AnotherEntity::class)->from(AnotherEntityMapper::class);
        });
    }
}