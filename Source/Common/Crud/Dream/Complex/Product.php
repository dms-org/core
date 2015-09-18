<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\ValueObjectCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Product extends Entity
{
    /**
     * @var int|null
     */
    public $categoryId;

    /**
     * @var int|null
     */
    public $categorySortIndex;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Money
     */
    public $price;

    /**
     * @var ProductImage[]|ValueObjectCollection
     */
    public $images;

    /**
     * Product constructor.
     *
     * @param ProductCategory|null $category
     * @param string               $name
     * @param Money                $price
     */
    public function __construct(ProductCategory $category = null, $name, Money $price)
    {
        parent::__construct();
        $this->setCategory($category);
        $this->name  = $name;
        $this->price = $price;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->categoryId)->nullable()->asInt();
        $class->property($this->categorySortIndex)->nullable()->asInt();
        $class->property($this->name)->asString();
        $class->property($this->price)->asObject(Money::class);
        $class->property($this->images)->asCollectionOf(ProductImage::class);
    }

    /**
     * @param ProductCategory $category
     *
     * @return void
     */
    private function setCategory(ProductCategory $category)
    {
        if ($this->categoryId !== $category->getId()) {
            $this->categoryId        = $category->getId();
            $this->categorySortIndex = $category->productIds->count() + 1;
        }
    }

    /**
     * @param string[] $filePaths
     *
     * @return void
     */
    public function setImages(array $filePaths)
    {
        foreach ($filePaths as $filePath) {
            $this->images[] = new ProductImage($filePath, count($this->images) + 1);
        }
    }
}