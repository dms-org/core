<?php declare(strict_types = 1);

namespace Dms\Core\Form\Object;

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
    public function __construct(string $property, FormObject $formInstance, array $embeddedFieldMap)
    {
        $this->property         = $property;
        $this->formInstance     = $formInstance;
        $this->embeddedFieldMap = $embeddedFieldMap;
    }

    /**
     * @return string
     */
    public function getProperty() : string
    {
        return $this->property;
    }

    /**
     * @return FormObject
     */
    public function getNewFormInstance() : FormObject
    {
        return clone $this->formInstance;
    }

    /**
     * Gets the embedded form field name indexed by their alias
     * name in the parent form.
     *
     * @return string[]
     */
    public function getEmbeddedFieldMap() : array
    {
        return $this->embeddedFieldMap;
    }
}