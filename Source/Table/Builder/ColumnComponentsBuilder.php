<?php

namespace Iddigital\Cms\Core\Table\Builder;

use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Field\Builder\FieldBuilderBase;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Util\Debug;

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