<?php

namespace Dms\Core\Form\Object;

use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Model\Object\ClassDefinition;

/**
 * The independent form object base class.
 *
 * This is designed for form objects with no dependencies and
 * hence the definitions can be created without any parameters.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class IndependentFormObject extends FormObject
{
    /**
     * @var FinalizedFormObjectDefinition[]
     */
    private static $definitions = [];

    public function __construct()
    {
        parent::__construct(static::formDefinition());
    }

    /**
     * @inheritDoc
     */
    final protected function defineClass(ClassDefinition $class)
    {
        return static::formDefinition()->getClass();
    }

    /**
     * Gets the class definition for the called class.
     *
     * @return FinalizedFormObjectDefinition
     */
    final public static function formDefinition()
    {
        $class = get_called_class();

        if (!isset(self::$definitions[$class])) {
            /** @var self $instance */
            $instance = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            $form     = new FormObjectDefinition(new ClassDefinition($instance, new \ReflectionClass(get_class($instance)), __CLASS__));
            $instance->defineForm($form);

            self::$definitions[$class] = $form->finalize();
        }

        return self::$definitions[$class];
    }

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     *
     * @return void
     */
    abstract protected function defineForm(FormObjectDefinition $form);

    /**
     * Gets the initial values of the form.
     *
     * @return array
     */
    final public static function initialValues()
    {
        /** @var self $newInstance */
        $newInstance = static::formDefinition()->getClass()->getCleanInstance();

        return $newInstance->getInitialValues();
    }

    /**
     * Gets the form defined by the called form object.
     *
     * @return IForm
     */
    final public static function asForm()
    {
        return static::formDefinition()->getForm();
    }

    /**
     * Builds an instance of the form object from the supplied
     * form submission data.
     *
     * @param array $submission
     *
     * @return static
     * @throws InvalidFormSubmissionException
     */
    final public static function build(array $submission)
    {
        /** @var self $newInstance */
        $newInstance = static::formDefinition()->getClass()->getCleanInstance();

        return $newInstance->submit($submission);
    }
}