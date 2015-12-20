<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Embedded;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Persistence\Db\Mapping\ReadModel\GenericReadModelMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithLabel extends ReadModel
{
    /**
     * @var GenericLabelReadModel
     */
    public $label;

    /**
     * GenericLabelReadModel constructor.
     *
     * @param GenericLabelReadModel $label
     */
    public function __construct(GenericLabelReadModel $label)
    {
        parent::__construct();
        $this->label = $label;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->label)->asObject(GenericLabelReadModel::class);
    }
}