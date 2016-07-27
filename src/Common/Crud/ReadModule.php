<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Definition\FinalizedReadModuleDefinition;
use Dms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\Definition\FinalizedModuleDefinition;
use Dms\Core\Module\Definition\ModuleDefinition;
use Dms\Core\Module\Module;
use Dms\Core\Module\ModuleLoadingContext;
use Dms\Core\Util\Debug;

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
     * @var IIdentifiableObjectSet
     */
    protected $dataSource;

    /**
     * @var callable
     */
    protected $labelCallback;

    /**
     * @var IObjectAction[]
     */
    private $objectActions = [];

    /**
     * @inheritDoc
     */
    public function __construct(IIdentifiableObjectSet $dataSource, IAuthSystem $authSystem)
    {
        $this->dataSource = $dataSource;

        parent::__construct($authSystem);
    }

    protected function loadNewDefinition() : ModuleDefinition
    {
        return new ReadModuleDefinition($this->dataSource, $this->authSystem);
    }

    /**
     * @inheritDoc
     */
    final protected function define(ModuleDefinition $definition)
    {
        /** @var ReadModuleDefinition $definition */
        $this->defineReadModule($definition);
    }

    protected function loadFromDefinition(FinalizedModuleDefinition $definition)
    {
        /** @var FinalizedReadModuleDefinition $definition */
        parent::loadFromDefinition($definition);

        $this->labelCallback = $this->definition->getLabelObjectCallback();

        foreach ($this->getParameterizedActions() as $name => $action) {
            if ($action instanceof IObjectAction) {
                $this->objectActions[$name] = $action;
            }
        }
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
    final public function getObjectType() : string
    {
        return $this->dataSource->getObjectType();
    }

    /**
     * @inheritDoc
     */
    final public function getDataSource() : IIdentifiableObjectSet
    {
        return $this->dataSource;
    }

    /**
     * @inheritDoc
     */
    final public function getLabelFor(ITypedObject $object) : string
    {
        $objectType = $this->dataSource->getObjectType();

        if (!($object instanceof $objectType)) {
            throw TypeMismatchException::format(
                'Invalid object supplied to %s: expecting type %s, %s given',
                __METHOD__, $objectType, Debug::getType($object)
            );
        }

        return call_user_func($this->labelCallback, $object);
    }

    /**
     * @inheritDoc
     */
    final public function getSummaryTable() : ISummaryTable
    {
        return $this->getTable(self::SUMMARY_TABLE);
    }

    /**
     * @inheritDoc
     */
    final public function getObjectActions() : array
    {
        return $this->objectActions;
    }

    /**
     * @inheritDoc
     */
    final public function hasObjectAction(string $name) : bool
    {
        return isset($this->objectActions[$name]);
    }

    /**
     * @inheritDoc
     */
    final public function getObjectAction(string $name) : IObjectAction
    {
        if (!isset($this->objectActions[$name])) {
            throw ActionNotFoundException::format(
                'Invalid name supplied to %s: expecting one of (%s), \'%s\' given',
                __METHOD__, Debug::formatValues(array_keys($this->objectActions)), $name
            );
        }

        return $this->objectActions[$name];
    }

    /**
     * @inheritDoc
     */
    final public function allowsDetails() : bool
    {
        return isset($this->objectActions[self::DETAILS_ACTION]);
    }

    /**
     * @inheritDoc
     */
    final public function getDetailsAction() : IObjectAction
    {
        if (!isset($this->objectActions[self::DETAILS_ACTION])) {
            throw UnsupportedActionException::format(
                'Cannot get details action in crud module for \'%s\': action is not supported',
                $this->getObjectType()
            );
        }

        return $this->objectActions[self::DETAILS_ACTION];
    }

    /**
     * @inheritDoc
     */
    final public function withDataSource(IIdentifiableObjectSet $dataSource) : IReadModule
    {
        if ($dataSource->getObjectType() !== $this->getObjectType()) {
            throw InvalidArgumentException::format(
                'Invalid data source supplied to %s: object type %s does not match required type %s',
                __METHOD__, $dataSource->getObjectType(), $this->getObjectType()
            );
        }

        return $this->loadModuleWithDataSource($dataSource);
    }

    protected function loadModuleWithDataSource(IIdentifiableObjectSet $dataSource) : IReadModule
    {
        return new static($dataSource, $this->authSystem);
    }
}