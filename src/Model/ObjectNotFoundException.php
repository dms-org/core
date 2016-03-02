<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception\BaseException;

/**
 * Exception for a non existent object.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ObjectNotFoundException extends BaseException
{
    public function __construct(string $objectType, int $index)
    {
        parent::__construct("Could not find an object of type {$objectType} at index \'{$index}\'");
    }
}
