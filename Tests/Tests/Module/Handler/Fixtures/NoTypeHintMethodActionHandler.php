<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler\Fixtures;

use Iddigital\Cms\Core\Module\Handler\ParameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Module\Mapping\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NoTypeHintMethodActionHandler extends ParameterizedActionHandler
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

    public function handle($foo)
    {

    }
}