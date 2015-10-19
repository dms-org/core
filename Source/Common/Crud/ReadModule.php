<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Module\Module;

/**
 * The read module base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ReadModule extends Module implements IReadModule
{
    /**
     * @inheritDoc
     */
    final protected function define(ModuleDefinition $module)
    {

    }


}