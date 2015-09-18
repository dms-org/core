<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductMapper extends EntityMapper
{
    /**
     * @var ProductCategoryMapper
     */
    protected $categoryMapper;

    /**
     * ProductMapper constructor.
     *
     * @param ProductCategoryMapper $categoryMapper
     */
    public function __construct(ProductCategoryMapper $categoryMapper)
    {
        parent::__construct('products');
        $this->categoryMapper = $categoryMapper;
    }

    public static function create()
    {
        return (new ProductCategoryMapper())->getProductMapper();
    }

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(Product::class);

        $map->column('category_id')->nullable()->asInt();
        $map->relation('categoryId')
                ->using($this->categoryMapper)
                ->manyToOneId()
                ->withRelatedIdAs('category_id');

        $map->property('categorySortIndex')->to('category_sort_index')->nullable()->asInt();
        $map->property('name')->to('name')->asVarchar(255);

        $map->embedded('price')->using(new MoneyMapper('price'));

        $map->embeddedCollection('images')
                ->toTable('product_images')
                ->withPrimaryKey('id')
                ->withForeignKeyToParentAs('product_id')
                ->usingCustom(function (MapperDefinition $map) {
                    $map->type(ProductImage::class);

                    $map->column('product_id')->asInt();
                    $map->property('filePath')->to('file_path')->asVarchar(255);
                    $map->property('sortIndex')->to('sort_index')->asInt();
                });
    }
}