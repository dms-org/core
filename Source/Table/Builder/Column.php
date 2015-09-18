<?php

namespace Iddigital\Cms\Core\Table\Builder;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Field\Builder\FieldBuilderBase;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;

/**
 * The column builder class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Column extends ColumnBuilderBase
{
    /**
     * Create a simple column for the supplied column component or form field.
     *
     * @param IColumnComponent|FieldBuilderBase|IField $componentOrField
     *
     * @return IColumn
     * @throws TypeMismatchException
     */
    public static function from($componentOrField)
    {
        if ($componentOrField instanceof IColumnComponent) {

        } elseif ($componentOrField instanceof IField) {
            $componentOrField = ColumnComponent::forField($componentOrField);
        } elseif ($componentOrField instanceof FieldBuilderBase) {
            $componentOrField = ColumnComponent::forField($componentOrField->build());
        } else {
            throw TypeMismatchException::argument(
                    __METHOD__,
                    'field',
                    IColumnComponent::class . '|' . IField::class . '|' . FieldBuilderBase::class,
                    $componentOrField
            );
        }

        return self::name($componentOrField->getName())->label($componentOrField->getLabel())->components([$componentOrField]);
    }

    /**
     * @param string $name
     *
     * @return ColumnLabelBuilder
     */
    public static function name($name)
    {
        $self       = new self();
        $self->name = $name;

        return new ColumnLabelBuilder($self);
    }
}