<?php

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Form\IFieldOptions;
use Dms\Core\Model\IEntitySet;

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

    public function __construct(IEntitySet $entities, callable $labelCallback = null)
    {
        $this->entities      = $entities;
        $this->labelCallback = $labelCallback;
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
    public function all()
    {
        $options = [];

        foreach ($this->entities->getAll() as $entity) {
            $options[] = new FieldOption(
                $entity->getId(),
                $this->labelCallback ? call_user_func($this->labelCallback, $entity) : $entity->getId()
            );
        }

        return $options;
    }
}