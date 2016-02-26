<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\InvalidInputException;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The array processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayAllProcessor extends FieldProcessor
{
    /**
     * @var IField
     */
    private $innerField;

    /**
     * @var array|null
     */
    private $initialValues;

    /**
     * @param IField     $innerField
     * @param IType|null $elementType
     * @param array      $initialValues
     */
    public function __construct(IField $innerField, IType $elementType = null, array $initialValues = null)
    {
        parent::__construct(Type::arrayOf($elementType ?: $innerField->getProcessedType()));

        $this->innerField    = $innerField;
        $this->initialValues = $initialValues;
    }

    protected function doProcess($input, array &$messages)
    {
        $processedInput = [];

        foreach ($input as $key => $element) {
            $field = isset($this->initialValues[$key])
                ? $this->innerField->withInitialValue($this->initialValues[$key])
                : $this->innerField;

            try {
                $processedInput[$key] = $field->process($element);
            } catch (InvalidInputException $e) {
                foreach ($e->getMessages() as $message) {
                    $messages[] = $message->withParameters([
                        'key' => $key,
                    ]);
                }
            }
        }

        return $processedInput;
    }

    protected function doUnprocess($input)
    {
        $unprocessedInput   = [];

        foreach ($input as $key => $element) {
            $field = isset($this->initialValues[$key])
                ? $this->innerField->withInitialValue($this->initialValues[$key])
                : $this->innerField;

            $unprocessedInput[$key] = $field->unprocess($element);
        }

        return $unprocessedInput;
    }
}