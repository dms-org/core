<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Action\Crud\CreateAction;
use Iddigital\Cms\Core\Common\Crud\Action\Crud\EditAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\Action\RemoveActionDefiner;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The crud module definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CrudModuleDefinition extends ReadModuleDefinition
{
    /**
     * @var IRepository
     */
    protected $dataSource;

    /**
     * @inheritDoc
     */
    public function __construct(IAuthSystem $authSystem, IRepository $dataSource)
    {
        parent::__construct($authSystem, $dataSource);
    }

    /**
     * @inheritDoc
     */
    public function crudForm(callable $formDefinitionCallback)
    {
        parent::crudForm($formDefinitionCallback);

        $this->loadCreateAction($formDefinitionCallback);
        $this->loadEditAction($formDefinitionCallback);
    }

    protected function loadCreateAction(callable $formDefinitionCallback)
    {
        $definition = $this->loadCrudFormDefinition(CrudFormDefinition::MODE_CREATE, $formDefinitionCallback);

        if (!$definition) {
            return;
        }

        $this->custom()->action(new CreateAction($this->dataSource, $this->authSystem, $definition));
    }

    protected function loadEditAction(callable $formDefinitionCallback)
    {
        $definition = $this->loadCrudFormDefinition(CrudFormDefinition::MODE_EDIT, $formDefinitionCallback);

        if (!$definition) {
            return;
        }

        $this->custom()->action(new EditAction($this->dataSource, $this->authSystem, $definition));
    }

    /**
     * Defines the remove object action.
     *
     * @return RemoveActionDefiner
     */
    public function removeAction()
    {
        return new RemoveActionDefiner($this->dataSource, $this->authSystem, function (IObjectAction $action) {
            $this->actions[$action->getName()] = $action;
        });
    }

}