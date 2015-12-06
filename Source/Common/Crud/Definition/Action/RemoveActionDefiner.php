<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectActionHandler;
use Iddigital\Cms\Core\Common\Crud\ICrudModule;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The remove object action definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RemoveActionDefiner extends ObjectActionDefiner
{
    /**
     * @var IEntitySet
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
    public function __construct(IRepository $dataSource, IAuthSystem $authSystem, callable $callback)
    {
        parent::__construct($dataSource, $authSystem, ICrudModule::REMOVE_ACTION, $callback);

        $this->dataSource = $dataSource;
        $this->authorize(ICrudModule::REMOVE_PERMISSION);
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
     * Defines the handler to delete the object from the
     * underlying data source.
     *
     * @return void
     */
    public function deleteFromRepository()
    {
        $this->handler(function (IEntity $object) {
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

        $this->currentObjectType = $this->dataSource->getObjectType();
        $this->currentDataDtoType = $handler->getDataDtoType();
        parent::handler(function (IEntity $object, $input = null) use ($handler) {

            foreach ($this->beforeRemoveCallbacks as $callback) {
                $callback($object, $input);
            }

            $handler->runOnObject($object, $input);

            foreach ($this->afterRemoveCallbacks as $callback) {
                $callback($object, $input);
            }
        });
    }
}