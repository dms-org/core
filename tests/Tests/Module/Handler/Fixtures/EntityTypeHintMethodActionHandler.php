<?php

namespace Dms\Core\Tests\Module\Handler\Fixtures;

use Dms\Core\Module\Handler\ParameterizedActionHandler;
use Dms\Core\Model\IEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityTypeHintMethodActionHandler extends ParameterizedActionHandler
{
    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    protected function getReturnType()
    {
        return IEntity::class;
    }

    public function handle(IEntity $foo)
    {

    }
}