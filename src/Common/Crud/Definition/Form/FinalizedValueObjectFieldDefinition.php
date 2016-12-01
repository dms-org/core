<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The finalized value object field definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedValueObjectFieldDefinition
{
    /**
     * @var FinalizedClassDefinition
     */
    protected $class;

    /**
     * @var FormWithBinding
     */
    protected $form;

    /**
     * @var callable
     */
    protected $createObjectCallback;

    /**
     * FinalizedValueObjectFieldDefinition constructor.
     *
     * @param FinalizedClassDefinition $class
     * @param FormWithBinding          $form
     * @param callable                 $createObjectCallback
     */
    public function __construct(FinalizedClassDefinition $class, FormWithBinding $form, callable $createObjectCallback)
    {
        $this->class                = $class;
        $this->form                 = $form;
        $this->createObjectCallback = $createObjectCallback;
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getClass(): FinalizedClassDefinition
    {
        return $this->class;
    }

    /**
     * @return FormWithBinding
     */
    public function getForm(): FormWithBinding
    {
        return $this->form;
    }

    /**
     * @return callable
     */
    public function getCreateObjectCallback(): callable
    {
        return $this->createObjectCallback;
    }

    /**
     * @param array $input
     *
     * @return ITypedObject
     */
    public function createNewObjectFromInput(array $input) : ITypedObject
    {
        return call_user_func($this->createObjectCallback, $input);
    }

    /**
     * Binds the supplied form data to the supplied object instance.
     *
     * @param ITypedObject $object
     * @param array        $processedInput
     *
     * @return void
     */
    public function bindToObject(ITypedObject $object, array $processedInput)
    {
        $applicableInput = array_intersect_key($processedInput, $this->form->getFields());
        $this->form->getBinding()->bindProcessedTo($object, $applicableInput);
    }
}