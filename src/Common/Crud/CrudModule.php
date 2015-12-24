<?php

namespace Dms\Core\Common\Crud;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\FinalizedCrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Dms\Core\Persistence\IRepository;

/**
 * The crud module base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CrudModule extends ReadModule implements ICrudModule
{
    /**
     * @var FinalizedCrudModuleDefinition
     */
    protected $definition;

    /**
     * @var IRepository
     */
    protected $dataSource;

    /**
     * @inheritDoc
     */
    public function __construct(IRepository $dataSource, IAuthSystem $authSystem)
    {
        parent::__construct($dataSource, $authSystem);
    }

    /**
     * @inheritDoc
     */
    final protected function defineReadModule(ReadModuleDefinition $module)
    {
        $definition = new CrudModuleDefinition($this->dataSource, $this->authSystem);

        $this->defineCrudModule($definition);

        return $definition->finalize();
    }

    /**
     * Defines the structure of this module.
     *
     * @param CrudModuleDefinition $module
     */
    abstract protected function defineCrudModule(CrudModuleDefinition $module);

    /**
     * @inheritDoc
     */
    final public function allowsCreate()
    {
        return $this->hasParameterizedAction(self::CREATE_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function getCreateAction()
    {
        if (!$this->hasParameterizedAction(self::CREATE_ACTION)) {
            throw UnsupportedActionException::format(
                    'Cannot get create action in crud module for \'%s\': action is not supported',
                    $this->getObjectType()
            );
        }

        return $this->getParameterizedAction(self::CREATE_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function allowsEdit()
    {
        return $this->hasObjectAction(self::EDIT_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function getEditAction()
    {
        if (!$this->hasObjectAction(self::EDIT_ACTION)) {
            throw UnsupportedActionException::format(
                    'Cannot get edit action in crud module for \'%s\': action is not supported',
                    $this->getObjectType()
            );
        }

        return $this->getObjectAction(self::EDIT_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function allowsRemove()
    {
        return $this->hasObjectAction(self::REMOVE_ACTION);
    }

    /**
     * @inheritDoc
     */
    final  public function getRemoveAction()
    {
        if (!$this->hasObjectAction(self::REMOVE_ACTION)) {
            throw UnsupportedActionException::format(
                    'Cannot get remove action in crud module for \'%s\': action is not supported',
                    $this->getObjectType()
            );
        }

        return $this->getObjectAction(self::REMOVE_ACTION);
    }
}