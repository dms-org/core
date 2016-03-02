<?php

namespace Dms\Core\Tests\Common\Crud\Modules\Fixtures\ValueObject;

use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\IValueObjectCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleValueObjectCrudModule extends CrudModule
{
    /**
     * @var IValueObjectCollection
     */
    protected $dataSource;

    /**
     * Defines the structure of this crud module
     *
     * @param CrudModuleDefinition $module
     */
    protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('value-object-crud-module');

        $module->labelObjects()->fromProperty(SimpleValueObject::DATA);

        $module->objectAction('duplicate-data')
            ->authorize(self::EDIT_PERMISSION)
            ->handler(function (SimpleValueObject $object) {
                $newObject = new SimpleValueObject($object->data . $object->data);
                $this->dataSource->update($object, $newObject);
            });

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Details', [
                $form->field(Field::name('data')->label('Data')->string()->required())
                    ->bindToProperty(SimpleValueObject::DATA),
            ]);
        });

        $module->removeAction()->deleteFromDataSource();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapProperty(SimpleValueObject::DATA)->to(Field::name('data')->label('Data')->string());
        });
    }
}