<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler;

use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Module\Handler\Fixtures\MethodActionHandler;
use Iddigital\Cms\Core\Tests\Module\Mapping\Fixtures\TestDto;

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