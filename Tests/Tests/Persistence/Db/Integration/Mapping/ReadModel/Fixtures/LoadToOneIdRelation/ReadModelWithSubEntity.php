<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToOneIdRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithSubEntity extends ReadModel
{
    /**
     * @var SubEntity
     */
    public $subEntity;

    /**
     * ReadModelWithLoadedToOneRelation constructor.
     *
     * @param SubEntity $subEntity
     */
    public function __construct(SubEntity $subEntity)
    {
        parent::__construct();
        $this->subEntity = $subEntity;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->subEntity)->asObject(SubEntity::class);
    }
}