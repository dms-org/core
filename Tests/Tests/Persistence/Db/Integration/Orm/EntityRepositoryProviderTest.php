<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityRepositoryProvider;
use Iddigital\Cms\Core\Persistence\DbRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\AnotherEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm\Fixtures\Relations\ManyToManyOrm;

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