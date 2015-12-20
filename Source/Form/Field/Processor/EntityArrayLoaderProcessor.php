<?php

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\ArrayType;

/**
 * The entity loader validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityArrayLoaderProcessor extends FieldProcessor
{
    /**
     * @var IEntitySet
     */
    private $entities;

    public function __construct(IEntitySet $entities)
    {
        parent::__construct(new ArrayType($entities->getElementType()));

        $this->entities = $entities;
    }

    protected function doProcess($input, array &$messages)
    {
        return $this->entities->getAllById($input);
    }

    protected function doUnprocess($input)
    {
        /** @var IEntity[] $input */
        $ids = [];

        foreach ($input as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }
}