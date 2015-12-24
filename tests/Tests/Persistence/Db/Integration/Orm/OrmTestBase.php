<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Orm;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Persistence\Db\Mapping\Orm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class OrmTestBase extends CmsTestCase
{
    /**
     * @var Orm
     */
    protected $orm;

    /**
     * @return Orm
     */
    abstract protected function loadOrm();

    public function setUp()
    {
        $this->orm = $this->loadOrm();
    }
}