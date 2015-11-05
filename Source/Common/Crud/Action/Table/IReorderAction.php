<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Table;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;

/**
 * The reorder object action interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IReorderAction extends IObjectAction
{
    public function runReorder($object);
}