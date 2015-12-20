<?php

namespace Dms\Core\Tests\Module\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\DataTransferObject;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestDto extends DataTransferObject
{
    /**
     * @var string|null
     */
    public $data;

    /**
     * TestDto constructor.
     *
     * @param string|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct();
        $this->data = $data;
    }


    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->data)->nullable()->asString();
    }
}