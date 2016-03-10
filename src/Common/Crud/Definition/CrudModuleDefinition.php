<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Action\Crud\CreateAction;
use Dms\Core\Common\Crud\Action\Crud\EditAction;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Definition\Action\RemoveActionDefiner;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Module\Definition\FinalizedModuleDefinition;

/**
 * The crud module definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CrudModuleDefinition extends ReadModuleDefinition
{
    /**
     * @var IMutableObjectSet
     */
    protected $dataSource;

    /**
     * @inheritDoc
     */
    public function __construct(IMutableObjectSet $dataSource, IAuthSystem $authSystem)
    {
        parent::__construct($dataSource, $authSystem);
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
    public function removeAction() : Action\RemoveActionDefiner
    {
        return new RemoveActionDefiner($this->dataSource, $this->authSystem, $this->requiredPermissions, function (IObjectAction $action) {
            $this->actions[$action->getName()] = $action;
        });
    }

    /**
     * @inheritDoc
     */
    public function finalize() : FinalizedModuleDefinition
    {
        $this->verifyCanBeFinalized();

        return new FinalizedCrudModuleDefinition(
            $this->name,
            $this->labelObjectCallback,
            $this->summaryTable,
            $this->requiredPermissions,
            $this->actions,
            $this->tables,
            $this->charts,
            $this->widgets
        );
    }
}