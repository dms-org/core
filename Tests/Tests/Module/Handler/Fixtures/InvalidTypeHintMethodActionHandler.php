<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler\Fixtures;

use Iddigital\Cms\Core\Module\Handler\ParameterizedActionHandler;
use Iddigital\Cms\Core\Model\IEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidTypeHintMethodActionHandler extends ParameterizedActionHandler
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

    public function handle(IEntity $foo)
    {

    }
}