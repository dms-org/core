<?php

namespace Iddigital\Cms\Core\Exception;

/**
 * Exception for an invalid argument.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidArgumentException extends BaseException
{
    use TypeAsserts;
}
