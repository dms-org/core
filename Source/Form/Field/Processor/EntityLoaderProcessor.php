<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;

/**
 * The entity loader validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityLoaderProcessor extends FieldProcessor
{
    /**
     * @var IEntitySet
     */
    private $entities;

    public function __construct(IEntitySet $entities)
    {
        parent::__construct($entities->getElementType());

        $this->entities = $entities;
    }

    protected function doProcess($input, array &$messages)
    {
        return $this->entities->get($input);
    }

    protected function doUnprocess($input)
    {
        /** @var IEntity $input */
        return $input->getId();
    }
}