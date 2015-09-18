<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Result;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageCreatedResult extends DataTransferObject
{
    /**
     * @var int
     */
    public $id;

    /**
     * PageCreatedResult constructor.
     *
     * @param int $id
     */
    public function __construct($id)
    {
        parent::__construct();
        $this->id = $id;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->id)->asInt();
    }
}