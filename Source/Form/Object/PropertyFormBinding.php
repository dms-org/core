<?php

namespace Iddigital\Cms\Core\Form\Object;

use Iddigital\Cms\Core\Model\Object\PropertyTypeDefiner;

/**
 * The fluent property form binding class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyFormBinding
{
    /**
     * @var PropertyTypeDefiner
     */
    private $propertyTypeDefiner;

    /**
     * @var callable
     */
    private $formCallback;

    /**
     * PropertyFormBinding constructor.
     *
     * @param PropertyTypeDefiner $propertyTypeDefiner
     * @param callable            $formCallback
     */
    public function __construct(PropertyTypeDefiner $propertyTypeDefiner, callable $formCallback)
    {
        $this->propertyTypeDefiner = $propertyTypeDefiner;
        $this->formCallback        = $formCallback;
    }

    /**
     * Binds the property to the supplied form.
     *
     * @param FormObject $innerForm
     *
     * @return void
     */
    public function to(FormObject $innerForm)
    {
        $this->propertyTypeDefiner->asObject($innerForm->getFormDefinition()->getClass()->getClassName());

        call_user_func($this->formCallback, $innerForm);
    }
}