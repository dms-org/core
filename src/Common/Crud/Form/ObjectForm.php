<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Form;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\Field\Builder\FieldNameBuilder;
use Dms\Core\Form\IForm;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\IObjectSetWithIdentityByIndex;

/**
 * The object form static factory class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectForm
{
    const INVALID_OBJECT_MESSAGE = 'validation.invalid-object';

    /**
     * @param IIdentifiableObjectSet $dataSource
     * @param callable|null          $objectValidationCallback
     *
     * @return IForm
     */
    public static function build(IIdentifiableObjectSet $dataSource, callable $objectValidationCallback = null) : IForm
    {
        return Form::create()->section('Object', [
            self::objectField(Field::create(), $dataSource, $objectValidationCallback),
        ])->build();
    }

    /**
     * Builds the field to load an entity from the supplied source.
     *
     * @param FieldNameBuilder       $fieldBuilder
     * @param IIdentifiableObjectSet $dataSource
     * @param callable|null          $objectValidationCallback
     *
     * @return FieldBuilderBase
     * @throws InvalidArgumentException
     */
    final public static function objectField(
        FieldNameBuilder $fieldBuilder,
        IIdentifiableObjectSet $dataSource,
        callable $objectValidationCallback = null
    ) : FieldBuilderBase
    {
        $field = $fieldBuilder
            ->name(IObjectAction::OBJECT_FIELD_NAME)
            ->label('Object');

        if ($dataSource instanceof IEntitySet) {
            $field = $field->entityFrom($dataSource);
        } elseif ($dataSource instanceof IObjectSetWithIdentityByIndex) {
            $field = $field->objectFromIndex($dataSource);
        } else {
            throw InvalidArgumentException::format(
                'Unknown object data source type: %s', get_class($dataSource)
            );
        }

        $field = $field->required();

        if ($objectValidationCallback) {
            $field->assert($objectValidationCallback, self::INVALID_OBJECT_MESSAGE);
        }

        return $field;
    }
}