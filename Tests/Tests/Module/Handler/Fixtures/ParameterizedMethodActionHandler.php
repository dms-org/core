<?php

namespace Dms\Core\Tests\Module\Handler\Fixtures;

use Dms\Core\Module\Handler\ParameterizedActionHandler;
use Dms\Core\Tests\Module\Mapping\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterizedMethodActionHandler extends ParameterizedActionHandler
{
    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    protected function getReturnType()
    {
        return ParamDto::class;
    }

    public function handle(ParamDto $data)
    {
        return ParamDto::from(123);
    }
}