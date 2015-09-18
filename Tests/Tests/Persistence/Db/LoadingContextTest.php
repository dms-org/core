<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\IdentityMap;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Tests\Persistence\Db\Fixtures\MockEntity;

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