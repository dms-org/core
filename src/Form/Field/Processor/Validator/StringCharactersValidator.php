<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The string characters validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringCharactersValidator extends FieldValidator
{
    const MESSAGE = 'validation.characters';

    /**
     * @var array
     */
    private $charRanges;

    public function __construct(IType $inputType, array $charRanges)
    {
        parent::__construct($inputType);
        $this->charRanges = $charRanges;
    }

    /**
     * Validates the supplied input and adds an
     * error messages to the supplied array.
     *
     * @param mixed     $input
     * @param Message[] $messages
     */
    protected function validate($input, array &$messages)
    {
        if ($input === '') {
            return;
        }

        $validChars = [];

        foreach ($this->charRanges as $startChar => $endChar) {
            $validChars += array_fill_keys(range($startChar, $endChar), true);
        }

        foreach (str_split($input) as $char) {
            if (!isset($validChars[$char])) {
                $messages[] = new Message(self::MESSAGE, ['valid_chars' => $this->formatCharRanges($this->charRanges)]);
                return;
            }
        }
    }

    private function formatCharRanges(array $charRanges)
    {
        $ranges = [];

        foreach ($charRanges as $startChar => $endChar) {
            $ranges[] = $startChar === $endChar ? $startChar : $startChar . '-' . $endChar;
        }

        return implode(', ', $ranges);
    }
}