<?php

namespace Dms\Core\Form\Object;

use Dms\Core\Form\Field\Builder\FieldBuilderBase;
use Dms\Core\Form\IField;

/**
 * The form object field binding builder
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormObjectFieldBindingBuilder
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * FormObjectFieldBindingBuilder constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Binds the property to the supplied field.
     *
     * @param IField|FieldBuilderBase $field
     *
     * @return IField
     */
    public function to($field)
    {
        if ($field instanceof FieldBuilderBase) {
            $field = $field->build();
        }

        call_user_func($this->callback, $field);

        return $field;
    }
}