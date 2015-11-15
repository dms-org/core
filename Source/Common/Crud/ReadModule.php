<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Definition\FinalizedReadModuleDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The read module base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ReadModule extends Module implements IReadModule
{
    /**
     * @var FinalizedReadModuleDefinition
     */
    protected $definition;

    /**
     * @var IEntitySet
     */
    protected $dataSource;

    /**
     * @var IObjectAction[]
     */
    private $objectActions = [];

    /**
     * @inheritDoc
     */
    public function __construct(IEntitySet $dataSource, IAuthSystem $authSystem)
    {
        $this->dataSource = $dataSource;
        parent::__construct($authSystem);

        foreach ($this->getParameterizedActions() as $name => $action) {
            if ($action instanceof IObjectAction) {
                $this->objectActions[$name] = $action;
            }
        }
    }


    /**
     * @inheritDoc
     */
    final protected function define(ModuleDefinition $module)
    {
        $definition = new ReadModuleDefinition($this->authSystem, $this->dataSource);

        $overrideDefinition = $this->defineReadModule($definition);

        if ($overrideDefinition) {
            return $overrideDefinition;
        }

        return $definition->finalize();
    }

    /**
     * Defines the structure of this read module
     *
     * @param ReadModuleDefinition $module
     */
    abstract protected function defineReadModule(ReadModuleDefinition $module);

    /**
     * @inheritDoc
     */
    final public function getObjectType()
    {
        return $this->dataSource->getObjectType();
    }

    /**
     * @inheritDoc
     */
    final public function getObjectSource()
    {
        return $this->dataSource;
    }

    /**
     * @inheritDoc
     */
    final public function getSummaryTable()
    {
        return $this->getTable(self::SUMMARY_TABLE);
    }

    /**
     * @inheritDoc
     */
    final public function getObjectActions()
    {
        return $this->objectActions;
    }

    /**
     * @inheritDoc
     */
    final  public function hasObjectAction($name)
    {
        return isset($this->objectActions[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function getObjectAction($name)
    {
        if (!isset($this->objectActions[$name])) {
            throw InvalidArgumentException::format(
                    'Invalid name supplied to %s: expecting one of (%s), %s given',
                    __METHOD__, Debug::formatValues(array_keys($this->objectActions)), $name
            );
        }

        return $this->objectActions[$name];
    }

    /**
     * @inheritDoc
     */
    final public function getSummaryTableAction()
    {
        return $this->getUnparameterizedAction(self::SUMMARY_TABLE_ACTION);
    }

    /**
     * @inheritDoc
     */
    final public function allowsDetails()
    {
        return isset($this->objectActions[self::DETAILS_ACTION]);
    }

    /**
     * @inheritDoc
     */
    final public function getDetailsAction()
    {
        if (!isset($this->objectActions[self::DETAILS_ACTION])) {
            throw UnsupportedActionException::format(
                    'Cannot get details action in crud module for \'%s\': action is not supported',
                    $this->getObjectType()
            );
        }

        return $this->objectActions[self::DETAILS_ACTION];
    }
}