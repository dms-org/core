<?php

namespace Dms\Core\Common\Crud\Dream\Complex;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Persistence\Db\Connection\IConnection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductModuleSchemaLevel extends CrudModule
{
    protected function loadRepository(IConnection $connection)
    {
        return new ProductRepository($connection);
    }

    protected function define(ModuleSchemaDefinition $module)
    {
        $module->name('product');
        $module->label('Product', 'Products');

        $module->form(function (FormStructureDefintion $form) {
            $form->section('Details', [
                    $form->property('categoryId')
                            ->from($this->categoryRepo)
                            ->labelledBy('name'),
                    $form->property('name')->field(Field::name('name')->label('Name')->string()->required()),
                    $form->property('price')->field(MoneyField::create('price', 'Price')),
                    $form->property('publishStatus')->field(Field::name('publish_status')->label('Publish Status')->enum())
            ]);

            $form->form(self::ADD_ACTION, function (ModuleSchemaDefinition $module) {

            });
        });

        $module->table(function (TableStructureDefinition $table, ComponentDefiner $component) {
            $table->from($this->productReadModelRepository);

            $table->column('Image Preview')->fromField('featured_image');

            $table->column('Product Name & Options')
                    ->name('name_or_options')
                    ->componens([
                            $component->fromField('name'),
                            $component->fromCallback('options', function (ProductReadModel $row) {
                                return count($row->product->options) . ' Options';
                            })
                    ]);

            $table->add(ItemDatesColumn::property('dates'));

            $table->column('Status')
                    ->name('status')
                    ->componens([
                            $component->fromField('publish_status')
                    ]);

            $table->view('Default')
                    ->default()
                    ->orderByAsc(['product_name']);

            $table->view('Categories')
                    ->orderByAsc(['category.name', 'category_sort_order'])
                    ->groupBy('category.id')
                    ->withSortColumnPropertyAs('categorySortOrder');
        });
    }
}