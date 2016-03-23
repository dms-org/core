<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Form\IFieldOption;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\Object\Entity;

/**
 * The entity options class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdOptions extends ObjectIdentityOptions
{
    /**
     * @var IEntitySet
     */
    protected $objects;

    /**
     * EntityIdOptions constructor.
     *
     * @param IEntitySet    $entities
     * @param callable|null $labelCallback
     * @param string|null   $labelMemberExpression
     * @param callable|null      $enabledCallback
     * @param callable|null      $disabledLabelCallback
     */
    public function __construct(
        IEntitySet $entities,
        callable $labelCallback = null,
        string $labelMemberExpression = null,
        callable $enabledCallback = null,
        callable $disabledLabelCallback = null
    ) {
        parent::__construct($entities, $labelCallback, $labelMemberExpression, $enabledCallback, $disabledLabelCallback);
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() : array
    {
        if ($this->objects instanceof IObjectSetWithLoadCriteriaSupport && $this->labelMemberExpression && !$this->enabledCallback) {
            return $this->loadOptionsViaOptimizedLoadCriteria($this->objects, $this->labelMemberExpression);
        }

        return parent::getAll();
    }

    /**
     * @param IObjectSetWithLoadCriteriaSupport $entities
     * @param null                              $labelMemberExpression
     *
     * @return IFieldOption[]
     */
    private function loadOptionsViaOptimizedLoadCriteria(
        IObjectSetWithLoadCriteriaSupport $entities,
        $labelMemberExpression = null
    ) : array
    {
        $criteria = $entities->loadCriteria();

        $criteria->load(Entity::ID, 'id');
        if ($labelMemberExpression) {
            $criteria->load($labelMemberExpression, 'label');
        }

        $options = [];

        foreach ($entities->loadMatching($criteria) as $item) {
            $options[] = new FieldOption(
                $item['id'],
                (string)(isset($item['label']) ? $item['label'] : $item['id'])
            );
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllValues() : array
    {
        if ($this->objects instanceof IObjectSetWithLoadCriteriaSupport) {
            return array_column(
                $this->objects->loadMatching(
                    $this->objects->loadCriteria()->load(Entity::ID)
                ),
                Entity::ID
            );
        }

        return parent::getAllValues();
    }

    /**
     * @param int    $index
     * @param object $object
     *
     * @return int
     */
    protected function getObjectIdentity(int $index, $object) : int
    {
        /** @var IEntity $object */
        return $object->getId();
    }
}