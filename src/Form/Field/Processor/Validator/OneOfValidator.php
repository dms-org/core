<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The one of validator that asserts that the input
 * is one of the supplied options.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneOfValidator extends FieldValidator
{
    const MESSAGE = 'validation.one-of';

    /**
     * @var IFieldOptions
     */
    protected $options;

    public function __construct(IType $inputType, IFieldOptions $options)
    {
        parent::__construct($inputType);
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        try {
            $option = $this->options->getOptionForValue($input);

            if ($option->isDisabled()) {
                throw InvalidArgumentException::format('Option is disabled');
            }
        } catch (InvalidArgumentException $e) {
            $messages[] = new Message(self::MESSAGE, ['options' => implode(', ', $this->options instanceof ArrayFieldOptions ? $this->options->getEnabledValues() : [])]);
        }
    }
}