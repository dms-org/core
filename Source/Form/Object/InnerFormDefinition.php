<?php

namespace Iddigital\Cms\Core\Form\Object;

/**
 * The inner form definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormDefinition
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var FormObject
     */
    private $formInstance;

    /**
     * @var string[]
     */
    private $embeddedFieldMap;

    /**
     * InnerFormDefinition constructor.
     *
     * @param string     $property
     * @param FormObject $formInstance
     * @param string[]   $embeddedFieldMap
     */
    public function __construct($property, FormObject $formInstance, array $embeddedFieldMap)
    {
        $this->property         = $property;
        $this->formInstance     = $formInstance;
        $this->embeddedFieldMap = $embeddedFieldMap;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return FormObject
     */
    public function getNewFormInstance()
    {
        return clone $this->formInstance;
    }

    /**
     * Gets the embedded form field name indexed by their alias
     * name in the parent form.
     *
     * @return string[]
     */
    public function getEmbeddedFieldMap()
    {
        return $this->embeddedFieldMap;
    }
}