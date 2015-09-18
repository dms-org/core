<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The field filter validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FilterValidator extends FieldValidator
{
    const MESSAGE = 'validation.filter';

    /**
     * @var int
     */
    private $filter;

    /**
     * @var mixed
     */
    private $options;

    public function __construct(IType $inputType, $filter, $options = null)
    {
        parent::__construct($inputType);
        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!filter_var($input, $this->filter, $this->options)) {
            $messages[] = new Message(static::MESSAGE);
        }
    }
}