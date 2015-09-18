<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The url filter validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UrlValidator extends FilterValidator
{
    const MESSAGE = 'validation.url';

    public function __construct(IType $inputType)
    {
        parent::__construct($inputType, FILTER_VALIDATE_URL);
    }
}