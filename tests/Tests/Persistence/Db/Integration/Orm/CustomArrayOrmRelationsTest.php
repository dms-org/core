<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Core\Persistence\Db\Mapping\CustomOrm;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntityMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\OneEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomArrayOrmRelationsTest extends OrmRelationsTest
{

    /**
     * @return Orm
     */
    protected function loadOrm()
    {
        return CustomOrm::from([
                OneEntity::class     => OneEntityMapper::class,
                AnotherEntity::class => AnotherEntityMapper::class,
        ]);
    }
}