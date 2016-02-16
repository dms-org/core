<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Form;

use Dms\Core\Form\Binding\FormBinding;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Binding\IFormBinding;
use Dms\Core\Form\ConflictingFieldNameException;
use Dms\Core\Form\Form;
use Dms\Core\Form\IFormProcessor;
use Dms\Core\Form\IFormSection;

/**
 * The form class that also contains a form object binding.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormWithBinding extends Form
{
    /**
     * @var IFormBinding
     */
    protected $binding;

    /**
     * @param IFormSection[]   $sections
     * @param IFormProcessor[] $processors
     * @param string           $bindingObjectType
     * @param IFieldBinding[]  $fieldBindings
     *
     * @throws ConflictingFieldNameException
     */
    public function __construct(array $sections, array $processors, string $bindingObjectType, array $fieldBindings)
    {
        parent::__construct($sections, $processors);

        $this->binding = new FormBinding($this, $bindingObjectType, $fieldBindings);
    }

    /**
     * Gets the object binding for the form.
     *
     * @return IFormBinding
     */
    public function getBinding() : IFormBinding
    {
        return $this->binding;
    }

    /**
     * @param object $object
     *
     * @return FormWithBinding
     */
    public function withInitialValuesFrom($object) : FormWithBinding
    {
        $form = $this->binding->getForm($object);

        return new self(
            $form->getSections(),
            $form->getProcessors(),
            $this->binding->getObjectType(),
            $this->binding->getFieldBindings()
        );
    }
}