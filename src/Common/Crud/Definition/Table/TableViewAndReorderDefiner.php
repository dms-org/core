<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Table;

use Dms\Core\Auth\IPermission;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Module\Definition\Table\TableViewDefiner;
use Dms\Core\Persistence\DbRepository;
use Dms\Core\Table\Criteria\ObjectRowCriteria;
use Dms\Core\Table\DataSource\ObjectTableDataSource;
use Dms\Core\Table\ITableDataSource;

/**
 * The table view and reorder action definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableViewAndReorderDefiner extends TableViewDefiner
{
    /**
     * @var ObjectRowCriteria
     */
    protected $rowCriteria;

    /**
     * @var callable
     */
    protected $reorderActionCallback;

    /**
     * @inheritDoc
     */
    public function __construct(ITableDataSource $dataSource, $name, $label, callable $reorderActionCallback)
    {
        parent::__construct($dataSource, $name, $label);
        $this->reorderActionCallback = $reorderActionCallback;
    }

    /**
     * @return IObjectSet
     */
    public function getObjectSet() : IObjectSet
    {
        return $this->rowCriteria->getObjectSet();
    }

    /**
     * @return Criteria
     */
    public function getObjectCriteria() : Criteria
    {
        return $this->rowCriteria->getObjectCriteria();
    }

    /**
     * @param callable $objectCriteriaCallback
     *
     * @return self
     */
    public function matches(callable $objectCriteriaCallback)
    {
        $this->rowCriteria->matches($objectCriteriaCallback);

        return $this;
    }

    /**
     * Defines that, when viewed with the this criteria, the table
     * rows can be reordered within their sections. The supplied callback
     * will be run when a row is reordered.
     *
     * Extra permissions to perform this reorder can be passed as the second
     * parameter. The {@see IReadModule::VIEW_PERMISSION} and {@see ICrudModule::EDIT_PERMISSION}
     * will be automatically applied to this action.
     *
     * The action name will default to the format "summary-table.{view-name}.reorder".
     *
     * Example:
     * <code>
     * ->withReorder(function (Person $person, $newIndex) {
     *      $this->dataSource->reorderPersonInGroup($person, $newIndex);
     * });
     * </code>
     *
     * @param callable      $callback
     * @param IPermission[] $permissions
     * @param string|null   $actionName
     *
     * @return static
     */
    public function withReorder(callable $callback, array $permissions = [], string $actionName = null)
    {
        call_user_func($this->reorderActionCallback, $callback, $permissions, $actionName);

        return $this;
    }

    /**
     * Defines that, when viewed with the this criteria, the table
     * rows can be reordered within their sections. The supplied callback
     * will be run when a row is reordered.
     *
     * Extra permissions to perform this reorder can be passed as the second
     * parameter. The {@see IReadModule::VIEW_PERMISSION} and {@see ICrudModule::EDIT_PERMISSION}
     * will be automatically applied to this action.
     *
     * The action name will default to the format "summary-table.{view-name}.reorder".
     *
     * Example:
     * <code>
     * ->withReorder(Person::ORDER_INDEX, Person::GENDER);
     * </code>
     *
     * @param string        $orderIndexProperty
     * @param string|null   $groupingColumnName
     * @param IPermission[] $permissions
     * @param string|null   $actionName
     *
     * @return static
     * @throws InvalidOperationException
     */
    public function withReorderOn(string $orderIndexProperty, string $groupingColumnName = null, array $permissions = [], string $actionName = null)
    {
        if (!($this->dataSource instanceof ObjectTableDataSource)) {
            throw InvalidOperationException::format(
                'Invalid call to %s: only supported on table data sources of type %s',
                __FUNCTION__, ObjectTableDataSource::class
            );
        }

        $repository = $this->dataSource->getObjectDataSource();

        if (!($repository instanceof DbRepository)) {
            throw InvalidOperationException::format(
                'Invalid call to %s: only supported on data sources of type %s',
                __FUNCTION__, DbRepository::class
            );
        }

        return $this->withReorder(
            function (Entity $entity, int $newIndex) use ($orderIndexProperty, $groupingColumnName, $repository) {
                $repository->reorderOnProperty($entity->getId(), $newIndex, $orderIndexProperty, $groupingColumnName);
            },
            $permissions,
            $actionName
        );
    }
}