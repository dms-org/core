<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToOneIdRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithSubEntityReadModel extends ReadModel
{
    /**
     * @var SubEntityReadModel
     */
    public $subEntity;

    /**
     * ReadModelWithLoadedToOneRelation constructor.
     *
     * @param SubEntityReadModel $subEntity
     */
    public function __construct(SubEntityReadModel $subEntity)
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
        $class->property($this->subEntity)->asObject(SubEntityReadModel::class);
    }
}