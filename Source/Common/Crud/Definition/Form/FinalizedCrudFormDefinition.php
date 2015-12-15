<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition\Form;

use Iddigital\Cms\Core\Common\Crud\Form\FormWithBinding;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\ITypedObject;

/**
 * The finalized CRUD form definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedCrudFormDefinition
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var IStagedForm
     */
    protected $stagedForm;

    /**
     * @var callable|null
     */
    protected $createObjectCallback;

    /**
     * @var callable[]
     */
    protected $onSubmitCallbacks;

    /**
     * @var  callable[]
     */
    protected $onSaveCallbacks;

    /**
     * FinalizedCrudFormDefinition constructor.
     *
     * @param string        $mode
     * @param IStagedForm   $stagedForm
     * @param callable|null $createObjectCallback
     * @param callable[]    $onSubmitCallbacks
     * @param callable[]    $onSaveCallbacks
     */
    public function __construct(
            $mode,
            IStagedForm $stagedForm,
            callable $createObjectCallback = null,
            array $onSubmitCallbacks,
            array $onSaveCallbacks
    ) {
        InvalidArgumentException::verify(
                $createObjectCallback || $mode !== CrudFormDefinition::MODE_CREATE,
                'create callback cannot be null for create form'
        );
        $this->mode                 = $mode;
        $this->stagedForm           = $stagedForm;
        $this->createObjectCallback = $createObjectCallback;
        $this->onSubmitCallbacks    = $onSubmitCallbacks;
        $this->onSaveCallbacks      = $onSaveCallbacks;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return IStagedForm
     */
    public function getStagedForm()
    {
        return $this->stagedForm;
    }

    /**
     * @return callable|null
     */
    public function getCreateObjectCallback()
    {
        return $this->createObjectCallback;
    }

    /**
     * @return callable[]
     */
    public function getOnSubmitCallbacks()
    {
        return $this->onSubmitCallbacks;
    }

    /**
     * @return callable[]
     */
    public function getOnSaveCallbacks()
    {
        return $this->onSaveCallbacks;
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
        foreach ($this->stagedForm->getAllStages() as $stage) {
            $form = $stage->loadForm($processedInput);

            if ($form instanceof FormWithBinding) {
                $applicableInput = array_intersect_key($processedInput, $form->getFields());
                $form->getBinding()->bindProcessedTo($object, $applicableInput);
            }
        }
    }

    /**
     * @param ITypedObject $object
     * @param array        $input
     *
     * @return void
     */
    public function invokeOnSubmitCallbacks(ITypedObject $object, array $input)
    {
        foreach ($this->onSubmitCallbacks as $callback) {
            $callback($object, $input);
        }
    }

    /**
     * @param ITypedObject $object
     * @param array        $input
     *
     * @return void
     */
    public function invokeOnSaveCallbacks(ITypedObject $object, array $input)
    {
        foreach ($this->onSaveCallbacks as $callback) {
            $callback($object, $input);
        }
    }
}