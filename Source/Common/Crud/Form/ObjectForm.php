<?php

namespace Iddigital\Cms\Core\Common\Crud\Form;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Field\Builder\FieldNameBuilder;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\IEntitySet;

/**
 * The object form static factory class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectForm
{
    /**
     * @param IEntitySet $dataSource
     *
     * @return IForm
     */
    public static function build(IEntitySet $dataSource)
    {
        return Form::create()->section('Object', [
                self::objectField(Field::create(), $dataSource)
        ])->build();
    }

    /**
     * Builds the field to load an entity from the supplied source.
     *
     * @param FieldNameBuilder $fieldBuilder
     * @param IEntitySet       $dataSource
     *
     * @return Field
     */
    final public static function objectField(FieldNameBuilder $fieldBuilder, IEntitySet $dataSource)
    {
        return $fieldBuilder
                ->name(IObjectAction::OBJECT_FIELD_NAME)
                ->label('Object')
                ->entityFrom($dataSource);
    }
}