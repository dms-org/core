<?php

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Form\IFieldOption;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;

/**
 * The entity options class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdOptions implements IFieldOptions
{
    /**
     * @var IEntitySet
     */
    private $entities;

    /**
     * @var callable
     */
    private $labelCallback;

    /**
     * @var string|null
     */
    private $labelMemberExpression;

    /**
     * EntityIdOptions constructor.
     *
     * @param IEntitySet    $entities
     * @param callable|null $labelCallback
     * @param string|null   $labelMemberExpression
     */
    public function __construct(IEntitySet $entities, callable $labelCallback = null, $labelMemberExpression = null)
    {
        $this->entities              = $entities;
        $this->labelCallback         = $labelCallback;
        $this->labelMemberExpression = $labelMemberExpression;
    }

    /**
     * @return IEntitySet
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll()
    {
        if ($this->entities instanceof IObjectSetWithLoadCriteriaSupport && !$this->labelCallback) {
            return $this->loadOptionsViaOptimizedLoadCriteria($this->entities, $this->labelMemberExpression);
        }

        $options = [];

        foreach ($this->entities->getAll() as $entity) {
            $options[] = new FieldOption(
                    $entity->getId(),
                    $this->labelCallback ? call_user_func($this->labelCallback, $entity) : $entity->getId()
            );
        }

        return $options;
    }

    /**
     * @param IObjectSetWithLoadCriteriaSupport $entities
     * @param null                              $labelMemberExpression
     *
     * @return IFieldOption[]
     */
    private function loadOptionsViaOptimizedLoadCriteria(IObjectSetWithLoadCriteriaSupport $entities, $labelMemberExpression = null)
    {
        $criteria = $entities->loadCriteria();

        $criteria->load('id');
        if ($labelMemberExpression) {
            $criteria->load($labelMemberExpression, 'label');
        }

        $options = [];

        foreach ($entities->loadMatching($criteria) as $item) {
            $options[] = new FieldOption(
                    $item['id'],
                    isset($item['label']) ? $item['label'] : $item['id']
            );
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllValues()
    {
        if ($this->entities instanceof IObjectSetWithLoadCriteriaSupport) {
            return array_column(
                    $this->entities->loadMatching(
                            $this->entities->loadCriteria()->load('id')
                    ),
                    'id'
            );
        }

        $ids      = [];
        $entities = $this->entities->getAll();

        foreach ($entities as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }
}