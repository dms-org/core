<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Embedded;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GenericLabelReadModel extends ReadModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * GenericLabelReadModel constructor.
     *
     * @param int    $id
     * @param string $label
     */
    public function __construct($id, $label)
    {
        parent::__construct();
        $this->id    = $id;
        $this->label = $label;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->id)->asInt();
        $class->property($this->label)->asString();
    }
}