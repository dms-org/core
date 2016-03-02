<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\Type\IType;

/**
 * The object id validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectIdValidator extends FieldValidator
{
    const MESSAGE = 'validation.object-id';

    /**
     * @var IIdentifiableObjectSet
     */
    private $objects;

    public function __construct(IType $inputType, IIdentifiableObjectSet $objects)
    {
        parent::__construct($inputType);
        $this->objects = $objects;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!$this->objects->has($input)) {
            $messages[] = new Message(self::MESSAGE, ['object_type' => $this->objects->getObjectType()]);
        }
    }
}