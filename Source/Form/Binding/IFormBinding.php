<?php

namespace Iddigital\Cms\Core\Form\Binding;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;

/**
 * The form binding interface.
 *
 * This defines a two-binding data between a form and a class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFormBinding
{
    /**
     * Gets bound object type.
     *
     * @return string
     */
    public function getObjectType();

    /**
     * Gets the form with initial values from the supplied object or none if NULL.
     *
     * @param object|null $object
     *
     * @return IForm
     * @throws TypeMismatchException
     */
    public function getForm($object = null);

    /**
     * Binds the form data to the supplied object.
     *
     * @param mixed $object
     * @param array $formSubmission
     *
     * @return void
     * @throws TypeMismatchException
     * @throws InvalidFormSubmissionException
     */
    public function bindTo($object, array $formSubmission);

    /**
     * Binds the submitted form data to the supplied object.
     *
     * @param mixed $object
     * @param array $processedSubmission
     *
     * @return void
     * @throws TypeMismatchException
     * @throws InvalidFormSubmissionException
     */
    public function bindProcessedTo($object, array $processedSubmission);

    /**
     * Returns whether contains a binding for the supplied form field.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasFieldBinding($name);

    /**
     * Gets field binding for the supplied form field.
     *
     * @param string $name
     *
     * @return IFieldBinding
     * @throws InvalidArgumentException
     */
    public function getFieldBinding($name);

    /**
     * Gets field bindings.
     *
     * @return IFieldBinding[]
     */
    public function getFieldBindings();
}