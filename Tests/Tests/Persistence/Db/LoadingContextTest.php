<?php

namespace Dms\Core\Tests\Persistence\Db;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\IEntity;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\IdentityMap;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Tests\Persistence\Db\Fixtures\MockEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadingContextTest extends CmsTestCase
{
    public function testGetters()
    {
        $context = $this->buildContext();

        $this->assertSame([], $context->getIdentityMaps());
    }

    public function testGetIdentityMap()
    {
        $context = $this->buildContext();

        $map = $context->getIdentityMap(MockEntity::class);
        $this->assertInstanceOf(IdentityMap::class, $map);
        $this->assertSame(MockEntity::class, $map->getEntityType());
        $this->assertSame($map, $context->getIdentityMap(MockEntity::class));


        $otherMap = $context->getIdentityMap(IEntity::class);
        $this->assertNotEquals($map, $otherMap);
    }

    /**
     * @return LoadingContext
     */
    protected function buildContext()
    {
        return new LoadingContext($this->getMockForAbstractClass(IConnection::class));
    }
}