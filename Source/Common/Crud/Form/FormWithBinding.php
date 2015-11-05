<?php

namespace Iddigital\Cms\Core\Common\Crud\Form;

use Iddigital\Cms\Core\Form\Binding\FormBinding;
use Iddigital\Cms\Core\Form\Binding\IFieldBinding;
use Iddigital\Cms\Core\Form\Binding\IFormBinding;
use Iddigital\Cms\Core\Form\ConflictingFieldNameException;
use Iddigital\Cms\Core\Form\Form;
use Iddigital\Cms\Core\Form\IFormProcessor;
use Iddigital\Cms\Core\Form\IFormSection;

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
    public function __construct(array $sections, array $processors, $bindingObjectType, array $fieldBindings)
    {
        parent::__construct($sections, $processors);

        $this->binding = new FormBinding($this, $bindingObjectType, $fieldBindings);
    }

    /**
     * Gets the object binding for the form.
     *
     * @return IFormBinding
     */
    public function getBinding()
    {
        return $this->binding;
    }
}