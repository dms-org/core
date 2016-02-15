<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * Exception for an invalid enum value.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidEnumValueException extends Exception\BaseException
{
    public function __construct($class, array $options, $supplied)
    {
        parent::__construct( sprintf(
                'Invalid enum value supplied to %s: expecting one of (%s), %s given',
                $class,
                implode(', ', array_map(function ($i) { return var_export($i, true); }, $options)),
                var_export($supplied, true)
        ));
    }

}