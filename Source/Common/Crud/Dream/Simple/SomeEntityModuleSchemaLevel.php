<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Simple;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Table\Builder\Table;

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