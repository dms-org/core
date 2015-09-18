<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductCategory extends Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $navigationSortIndex;

    /**
     * @var int[]|EntityIdCollection
     */
    public $productIds;

    /**
     * ProductCategory constructor.
     *
     * @param string $name
     * @param int    $navigationSortIndex
     */
    public function __construct($name, $navigationSortIndex)
    {
        parent::__construct();
        $this->name                = $name;
        $this->navigationSortIndex = $navigationSortIndex;
        $this->productIds          = new EntityIdCollection();
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
        $class->property($this->navigationSortIndex)->asInt();
        $class->property($this->productIds)->asObject(EntityIdCollection::class);
    }
}