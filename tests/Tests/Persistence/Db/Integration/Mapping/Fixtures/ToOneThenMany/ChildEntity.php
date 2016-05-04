<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChildEntity extends Entity
{
    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        
    }
}