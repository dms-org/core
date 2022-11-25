<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

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

    public function __construct(IType $inputType, $filter, $options = [])
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
        if (!filter_var($input, $this->filter, $this->options ?? [])) {
            $messages[] = new Message(static::MESSAGE);
        }
    }
}
