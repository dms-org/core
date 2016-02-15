<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Model\Type\IType;

/**
 * The email filter validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmailValidator extends FilterValidator
{
    const MESSAGE = 'validation.email';

    public function __construct(IType $inputType)
    {
        parent::__construct($inputType, FILTER_VALIDATE_EMAIL);
    }
}