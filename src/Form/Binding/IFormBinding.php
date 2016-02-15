<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;

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
    public function getObjectType() : string;

    /**
     * Gets the form with initial values from the supplied object or none if NULL.
     *
     * @param object|null $object
     *
     * @return IForm
     * @throws TypeMismatchException
     */
    public function getForm($object = null) : \Dms\Core\Form\IForm;

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
    public function hasFieldBinding(string $name) : bool;

    /**
     * Gets field binding for the supplied form field.
     *
     * @param string $name
     *
     * @return IFieldBinding
     * @throws InvalidArgumentException
     */
    public function getFieldBinding(string $name) : IFieldBinding;

    /**
     * Gets field bindings.
     *
     * @return IFieldBinding[]
     */
    public function getFieldBindings() : array;
}