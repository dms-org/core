<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Action;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Dms\Core\Common\Crud\Action\Object\IObjectActionHandler;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Model\ITypedObject;

/**
 * The remove object action definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RemoveActionDefiner extends ObjectActionDefiner
{
    /**
     * @var IMutableObjectSet
     */
    protected $dataSource;

    /**
     * @var callable[]
     */
    protected $beforeRemoveCallbacks = [];

    /**
     * @var callable[]
     */
    protected $afterRemoveCallbacks = [];

    /**
     * @inheritDoc
     */
    public function __construct(IMutableObjectSet $dataSource, IAuthSystem $authSystem, array $requiredPermissions, callable $callback)
    {
        parent::__construct($dataSource, $authSystem, $requiredPermissions, ICrudModule::REMOVE_ACTION, $callback);

        $this->dataSource = $dataSource;
        $this->authorize(ICrudModule::REMOVE_PERMISSION);
        $this->returns($dataSource->getObjectType());
    }

    /**
     * Defines a callback to be called before the object
     * is removed.
     *
     * Example:
     * <code>
     * ->beforeRemove(function (Person $object, ArrayDataObject $input) {
     *      // ...
     * });
     * </code>
     *
     * @param callable $callback
     *
     * @return static
     */
    public function beforeRemove(callable $callback)
    {
        $this->beforeRemoveCallbacks[] = $callback;

        return $this;
    }

    /**
     * Defines a callback to be called after the object
     * is removed.
     *
     * Example:
     * <code>
     * ->afterRemove(function (Person $object, ArrayDataObject $input) {
     *      // ...
     * });
     * </code>
     *
     * @param callable $callback
     *
     * @return static
     */
    public function afterRemove(callable $callback)
    {
        $this->afterRemoveCallbacks[] = $callback;

        return $this;
    }

    /**
     * Defines the handler to delete the object from the underlying data source.
     *
     * @return void
     */
    public function deleteFromDataSource()
    {
        $this->handler(function (ITypedObject $object) {
            $this->dataSource->remove($object);
        });
    }

    /**
     * @inheritDoc
     */
    public function handler($handler)
    {
        if (!($handler instanceof IObjectActionHandler)) {
            $handler = new CustomObjectActionHandler($handler);
        }

        $this->currentObjectType  = $this->dataSource->getObjectType();
        $this->currentDataDtoType = $handler->getDataDtoType();

        parent::handler(function (ITypedObject $object, $input = null) use ($handler) {

            foreach ($this->beforeRemoveCallbacks as $callback) {
                $callback($object, $input);
            }

            $handler->runOnObject($object, $input);

            foreach ($this->afterRemoveCallbacks as $callback) {
                $callback($object, $input);
            }

            return $object;
        });
    }
}