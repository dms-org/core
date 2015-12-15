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
    const INVALID_OBJECT_MESSAGE = 'validation.invalid-object';

    /**
     * @param IEntitySet $dataSource
     * @param callable|null $objectValidationCallback
     *
     * @return IForm
     */
    public static function build(IEntitySet $dataSource, callable $objectValidationCallback = null)
    {
        return Form::create()->section('Object', [
                self::objectField(Field::create(), $dataSource, $objectValidationCallback)
        ])->build();
    }

    /**
     * Builds the field to load an entity from the supplied source.
     *
     * @param FieldNameBuilder $fieldBuilder
     * @param IEntitySet       $dataSource
     * @param callable|null    $objectValidationCallback
     *
     * @return Field
     */
    final public static function objectField(
            FieldNameBuilder $fieldBuilder,
            IEntitySet $dataSource,
            callable $objectValidationCallback = null
    ) {
        $field = $fieldBuilder
                ->name(IObjectAction::OBJECT_FIELD_NAME)
                ->label('Object')
                ->entityFrom($dataSource)
                ->required();

        if ($objectValidationCallback) {
            $field->assert($objectValidationCallback, self::INVALID_OBJECT_MESSAGE);
        }

        return $field;
    }
}