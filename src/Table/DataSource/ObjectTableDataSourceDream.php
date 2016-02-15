<?php declare(strict_types = 1);

namespace Dms\Core\Table\DataSource;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Table\DataSource\Definition\ObjectTableDefinition;

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