<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\FinalizedCrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Module\IParameterizedAction;

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
    final public function allowsCreate() : bool
    {
        return $this->hasParameterizedAction(self::CREATE_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function getCreateAction() : IParameterizedAction
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
    final public function allowsEdit() : bool
    {
        return $this->hasObjectAction(self::EDIT_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function getEditAction() : IObjectAction
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
    final public function allowsRemove() : bool
    {
        return $this->hasObjectAction(self::REMOVE_ACTION);
    }

    /**
     * @inheritDoc
     */
    final  public function getRemoveAction() : IObjectAction
    {
        if (!$this->hasObjectAction(self::REMOVE_ACTION)) {
            throw UnsupportedActionException::format(
                    'Cannot get remove action in crud module for \'%s\': action is not supported',
                    $this->getObjectType()
            );
        }

        return $this->getObjectAction(self::REMOVE_ACTION);
    }

    final protected function loadModuleWithDataSource(IIdentifiableObjectSet $dataSource) : IReadModule
    {
        if (!($dataSource instanceof IMutableObjectSet)) {
            throw InvalidArgumentException::format(
                    'Invalid data source supplied to %s: data source must be an instance of %s, %s given',
                    __METHOD__, IMutableObjectSet::class, get_class($dataSource)
            );
        }

        return $this->loadCrudModuleWithDataSource($dataSource);
    }

    protected function loadCrudModuleWithDataSource(IMutableObjectSet $dataSource) : ICrudModule
    {
        return new static($dataSource, $this->authSystem);
    }
}