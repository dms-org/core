<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The entity id array validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdArrayValidator extends FieldValidator
{
    const MESSAGE = 'validation.entity-id-array';

    /**
     * @var IEntitySet
     */
    private $entities;

    public function __construct(IType $inputType, IEntitySet $entities)
    {
        parent::__construct($inputType);
        $this->entities = $entities;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!$this->entities->hasAll($input)) {
            $messages[] = new Message(
                    self::MESSAGE,
                    ['entity_type' => $this->entities->getEntityType()]
            );
        }
    }
}