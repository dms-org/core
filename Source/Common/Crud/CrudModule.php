<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Common\Crud\Form\RemoveEntityDto;
use Iddigital\Cms\Core\Common\Crud\Handler\RemoveEntityHandler;
use Iddigital\Cms\Core\Module\Module;

/**
 * The crud context base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CrudModule extends Module implements ICrudModule
{
    /**
     * @inheritDoc
     */
    public function getRemoveAction()
    {
        return $this->bind(new RemoveEntityDto())
                ->to(new RemoveEntityHandler($this->getRepository()))
                ->authorize($this->getRemovePermission());
    }


}