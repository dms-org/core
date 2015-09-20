<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Orm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntityMapper;

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