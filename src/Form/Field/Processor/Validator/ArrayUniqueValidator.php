<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Util\Hashing\ValueHasher;

/**
 * The validator that checks whether all elements in an array are unique.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayUniqueValidator extends FieldValidator
{
    const MESSAGE = 'validation.array-contains-duplicates';

    /**
     * Validates the supplied input and adds an
     * error messages to the supplied array.
     *
     * @param mixed     $input
     * @param Message[] $messages
     */
    protected function validate($input, array &$messages)
    {
        $uniqueValues = [];

        foreach ($input as $element) {
            $uniqueValues[ValueHasher::hash($element)] = true;
        }

        if (count($uniqueValues) !== count($input)) {
            $messages[] = new Message(self::MESSAGE);
        }
    }
}