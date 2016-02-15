<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Model\Type\IType;

/**
 * The ip address filter validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IpAddressValidator extends FilterValidator
{
    const MESSAGE = 'validation.ip-address';

    public function __construct(IType $inputType)
    {
        parent::__construct($inputType, FILTER_VALIDATE_IP);
    }
}