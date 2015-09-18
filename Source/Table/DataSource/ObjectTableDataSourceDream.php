<?php

namespace Iddigital\Cms\Core\Table\DataSource;

use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\DataSource\Definition\ObjectTableDefinition;

/**
 * The typed object table data source.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class baggsggfgdgdf
{
    protected function define(ObjectTableDefinition $map)
    {
        $map->type(SomeEntity::class);

        $map->property('category')->to(EntityLabelColumn::column('category'));
        $map->property('data')->to(Field::name('foo')->label('Foo')->string()->required());
        $map->column(Name::column);

        $map->computed(function (SomeEntity $entity) {
            return [
                    ActionButton::edit(),
                    ActionButton::delete(),
                    ActionButton::clone_(),
            ];
        })->to(ActionListColumn::create());

        $map->sorting()
                ->whenGroupedBy(['category.id'])
                ->whenOrderedBy(['category_sort_order'])
                ->to(ReorderAction::property('categorySortIndex'));
    }
}