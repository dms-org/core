<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\ToOneRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneRelation\SubEntity;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithToOneRelation extends ReadModel
{
    /**
     * @var SubEntity
     */
    public $subEntity;

    /**
     * ReadModelWithToOneRelation constructor.
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