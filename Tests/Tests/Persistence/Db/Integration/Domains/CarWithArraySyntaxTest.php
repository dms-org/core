<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Mapper\CarMapperWithArraySyntax;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CarWithArraySyntaxTest extends CarTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CarMapperWithArraySyntax::orm();
    }
}