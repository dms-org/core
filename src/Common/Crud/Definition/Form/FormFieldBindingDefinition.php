<?php

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\IField;

/**
 * The field binding definition class..
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormFieldBindingDefinition
{
    /**
     * @var IField
     */
    protected $field;

    /**
     * @var IFieldBinding|null
     */
    protected $binding;

    /**
     * FormFieldBindingDefinition constructor.
     *
     * @param IField             $field
     * @param IFieldBinding|null $binding
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IField $field, IFieldBinding $binding = null)
    {
        if ($binding && $field->getName() !== $binding->getFieldName()) {
            throw InvalidArgumentException::format(
                    'Field name \'%s\' must match binding field name \'%s\'',
                    $field->getName(),
                    $binding->getFieldName());
        }

        $this->field   = $field;
        $this->binding = $binding;
    }

    /**
     * @return IField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return bool
     */
    public function hasBinding()
    {
        return $this->binding !== null;
    }

    /**
     * @return IFieldBinding|null
     */
    public function getBinding()
    {
        return $this->binding;
    }
}