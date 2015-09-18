<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToOneIdRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubEntityReadModel extends ReadModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $val;

    /**
     * SubEntityReadModel constructor.
     *
     * @param int $id
     * @param int $val
     */
    public function __construct($id, $val)
    {
        parent::__construct();
        $this->id  = $id;
        $this->val = $val;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->id)->asInt();
        $class->property($this->val)->asInt();
    }
}