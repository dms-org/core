<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Orm;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Orm;

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