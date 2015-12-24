<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Car extends Entity
{
    /**
     * @var string
     */
    public $brand;

    /**
     * Car constructor.
     *
     * @param int|null $id
     * @param string   $brand
     */
    public function __construct($id, $brand)
    {
        parent::__construct($id);
        $this->brand = $brand;
    }

    /**
     * @inheritDoc
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->brand)->asString();
    }
}