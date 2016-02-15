<?php declare(strict_types = 1);

namespace Dms\Core\Exception;

/**
 * Exception for an invalid return valid.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidReturnValueException extends BaseException
{
    use TypeAsserts;
}
