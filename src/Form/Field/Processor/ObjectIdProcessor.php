<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\Validator\ObjectIdValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityCollection;

/**
 * The object id validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectIdProcessor extends FieldProcessor
{
    /**
     * @param mixed $input
     * @param array $messages
     *
     * @return mixed
     */
    protected function doProcess($input, array &$messages)
    {
        if (is_string($input) && strpos($input, EntityCollection::ENTITY_WITHOUT_ID_PREFIX) === 0) {
            return $input;
        }

        if (is_int($input) || ctype_digit($input)) {
            return (int)$input;
        }

        $messages[] = new Message(ObjectIdValidator::MESSAGE);

        return null;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    protected function doUnprocess($input)
    {
        return $input;
    }
}