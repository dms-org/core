<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Model\Type\IType;

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