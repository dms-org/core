<?php declare(strict_types = 1);

namespace Dms\Core\Table\Builder;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\IField;
use Dms\Core\Table\Column\Component\ColumnComponent;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;

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
    public static function from($componentOrField) : \Dms\Core\Table\IColumn
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
    public static function name(string $name) : ColumnLabelBuilder
    {
        $self       = new self();
        $self->name = $name;

        return new ColumnLabelBuilder($self);
    }
}