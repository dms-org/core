<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Common\Crud\Form\FormWithBinding;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\ITypedObject;

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
        string $mode,
        IStagedForm $stagedForm,
        callable $createObjectCallback = null,
        array $onSubmitCallbacks,
        array $onSaveCallbacks
    )
    {
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
    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * @return IStagedForm
     */
    public function getStagedForm() : IStagedForm
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
    public function getOnSubmitCallbacks() : array
    {
        return $this->onSubmitCallbacks;
    }

    /**
     * @return callable[]
     */
    public function getOnSaveCallbacks() : array
    {
        return $this->onSaveCallbacks;
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