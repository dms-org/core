<?php

namespace Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple;

use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleCrudModule extends CrudModule
{

    /**
     * Defines the structure of this crud module
     *
     * @param CrudModuleDefinition $module
     */
    protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('simple-crud-module');

        $module->labelObjects()->fromProperty('data');

        $module->objectAction('duplicate-data')
                ->authorize(self::EDIT_PERMISSION)
                ->handler(function (SimpleEntity $entity) {
                    $entity->data .= $entity->data;
                    $this->dataSource->save($entity);
                });

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Details', [
                    $form->field(Field::name('data')->label('Data')->string()->required())
                            ->bindToProperty(SimpleEntity::DATA),
            ]);
        });

        $module->removeAction()->deleteFromRepository();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapProperty(SimpleEntity::DATA)->to(Field::name('data')->label('Data')->string());
        });
    }
}