<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ToOneThenMany;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubEntity extends Entity
{
    /**
     * @var EntityIdCollection
     */
    public $childIds;

    /**
     * SubEntity constructor.
     *
     * @param int|null $id
     * @param array    $childIds
     */
    public function __construct($id, array $childIds = [])
    {
        parent::__construct($id);
        $this->childIds = new EntityIdCollection($childIds);
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->childIds)->asType(EntityIdCollection::type());
    }
}