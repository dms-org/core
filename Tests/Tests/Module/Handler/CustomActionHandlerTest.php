<?php

namespace Dms\Core\Tests\Module\Handler;

use Dms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Dms\Core\Module\IUnparameterizedActionHandler;
use Dms\Core\Tests\Module\Mapping\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomActionHandlerTest extends ActionHandlerTest
{
    /**
     * @return IUnparameterizedActionHandler
     */
    protected function handler()
    {
        return new CustomUnparameterizedActionHandler(function () {
            return TestDto::from(false);
        }, TestDto::class);
    }

    protected function expectedReturnValue()
    {
        return TestDto::from(false);
    }
}