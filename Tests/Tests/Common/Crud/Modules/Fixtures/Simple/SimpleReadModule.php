<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Simple;

use Iddigital\Cms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\ReadModuleDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Iddigital\Cms\Core\Common\Crud\ReadModule;
use Iddigital\Cms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleReadModule extends ReadModule
{

    /**
     * Defines the structure of this read module
     *
     * @param ReadModuleDefinition $module
     */
    protected function defineReadModule(ReadModuleDefinition $module)
    {
        $module->name('simple-read-module');

        $module->labelObjects()->fromProperty('data');

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Details', [
                    $form->field(Field::name('data')->label('Data')->string()->required())
                            ->bindToProperty(SimpleEntity::DATA),
            ]);
        });

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapProperty(SimpleEntity::DATA)->to(Field::name('data')->label('Data')->string());
        });
    }
}