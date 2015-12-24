<?php

namespace Dms\Core\Tests\Module\Handler;

use Dms\Core\Module\IUnparameterizedActionHandler;
use Dms\Core\Tests\Module\Handler\Fixtures\MethodActionHandler;
use Dms\Core\Tests\Module\Mapping\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MethodActionHandlerTest extends ActionHandlerTest
{
    /**
     * @return IUnparameterizedActionHandler
     */
    protected function handler()
    {
        return new MethodActionHandler(TestDto::from(true));
    }

    protected function expectedReturnValue()
    {
        return TestDto::from(true);
    }
}