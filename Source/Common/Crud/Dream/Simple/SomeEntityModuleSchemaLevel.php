<?php

namespace Dms\Core\Common\Crud\Dream\Simple;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IForm;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Table\Builder\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SomeEntityModuleSchemaLevel extends CrudModule
{
    protected function loadRepository(IConnection $connection)
    {
        return new SomeEntityRepository($connection);
    }

    protected function schema(ModuleSchemaDefinition $module)
    {
        $module->name('some-entity');
        $module->label('Some Entity', 'Some Entities');

        $module->property('data')
            ->field(Field::name('data')->label('Data')->string()->required()->maxLength(500))
            ->asColumn();

        $module->s
        $module->crudActions();
    }
}