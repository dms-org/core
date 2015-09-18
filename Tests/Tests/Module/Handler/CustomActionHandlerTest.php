<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler;

use Iddigital\Cms\Core\Module\Handler\CustomUnparameterizedActionHandler;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Module\Mapping\Fixtures\TestDto;

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