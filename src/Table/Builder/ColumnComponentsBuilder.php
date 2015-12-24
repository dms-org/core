<?php

namespace Dms\Core\Table\Builder;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\IField;
use Dms\Core\Table\Column\Component\ColumnComponent;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Util\Debug;

/**
 * The column component builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentsBuilder extends ColumnBuilderBase
{
    /**
     * @param IField[]|FieldBuilderBase[]|IColumnComponent[] $components
     *
     * @return IColumn
     * @throws TypeMismatchException
     */
    public function components(array $components)
    {
        foreach ($components as $key => $component) {
            if ($component instanceof IColumnComponent) {

            } elseif ($component instanceof IField) {
                $component = ColumnComponent::forField($component);
            } elseif ($component instanceof FieldBuilderBase) {
                $component = ColumnComponent::forField($component->build());
            } else {
                throw TypeMismatchException::format(
                        'Invalid array passed to %s: expecting elements to be instance of %s, %s found',
                        __METHOD__, IColumnComponent::class . '|' . IField::class . '|' . FieldBuilderBase::class, Debug::getType($component)
                );
            }

            $components[$key] = $component;
        }

        $this->components = $components;

        return $this->build();
    }
}