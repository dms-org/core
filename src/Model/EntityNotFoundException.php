<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception\BaseException;

/**
 * Exception for a non existent entity.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class EntityNotFoundException extends BaseException
{
    public function __construct($entityType, $id, $idProperty = IEntity::ID)
    {
        parent::__construct("Could not find an entity of type {$entityType} with property \'{$idProperty}\' = {$id}");
    }

}
