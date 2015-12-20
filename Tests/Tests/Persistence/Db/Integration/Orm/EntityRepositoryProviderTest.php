<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\EntityRepositoryProvider;
use Dms\Core\Persistence\DbRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntity;
use Dms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\ManyToManyOrm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityRepositoryProviderTest extends CmsTestCase
{
    /**
     * @var EntityRepositoryProvider
     */
    protected $provider;

    public function testProvider()
    {
        $orm        = new ManyToManyOrm();
        $connection = $this->getMockForAbstractClass(IConnection::class);
        $provider   = new EntityRepositoryProvider($orm, $connection);

        $this->assertSame($orm, $provider->getOrm());
        $this->assertSame($connection, $provider->getConnection());

        /** @var DbRepository $dataSource */
        $dataSource = $provider->loadDataSourceFor(AnotherEntity::class);

        $this->assertInstanceOf(DbRepository::class, $dataSource);
        $this->assertSame($connection, $dataSource->getConnection());
        $this->assertSame($orm->getEntityMapper(AnotherEntity::class), $dataSource->getMapper());

        $this->assertThrows(function () use ($provider) {
            $provider->loadDataSourceFor('SomeInvalidEntity');
        }, InvalidArgumentException::class);
    }
}