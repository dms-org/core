<?php

namespace Dms\Core\Common\Crud\Dream\Complex;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductCategoryMapper extends EntityMapper
{
    /**
     * @var ProductMapper
     */
    protected $productMapper;

    public function __construct()
    {
        parent::__construct('');
        $this->productMapper = new ProductMapper($this);
    }

    /**
     * @return ProductMapper
     */
    public function getProductMapper()
    {
        return $this->productMapper;
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
        $map->type(ProductCategory::class);
        $map->toTable('product_categories');

        $map->property('name')->to('name')->asVarchar(255);
        $map->property('navigationSortIndex')->to('navigation_sort_index')->asInt();

        $map->relation('productIds')
            ->using($this->productMapper)
            ->toManyIds()
            ->withParentIdAs('category_id');
    }
}