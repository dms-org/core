<?php

namespace Dms\Core\Tests\Module\Handler\Fixtures;

use Dms\Core\Module\Handler\ParameterizedActionHandler;
use Dms\Core\Tests\Module\Mapping\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PrivateMethodActionHandler extends ParameterizedActionHandler
{
    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    protected function getReturnType()
    {
        return null;
    }

    private function handle(ParamDto $dto)
    {

    }
}