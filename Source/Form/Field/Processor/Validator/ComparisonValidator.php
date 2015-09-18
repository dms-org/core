<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The comparison validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ComparisonValidator extends FieldValidator
{
    /**
     * @var mixed
     */
    protected $value;

    public function __construct(IType $inputType, $value)
    {
        parent::__construct($inputType);
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}