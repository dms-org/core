<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\BaseException;

/**
 * Exception for an duplicate field names in a form.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ConflictingFieldNameException extends BaseException
{
    public function __construct($fieldName)
    {
        parent::__construct("Cannot build form: field '{$fieldName}' has already been defined");
    }

}
