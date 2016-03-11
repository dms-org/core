<?php declare(strict_types = 1);

namespace Dms\Core\Form\Object;

use Dms\Core\Form\IForm;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The finalized form object definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedFormObjectDefinition
{
    /**
     * @var FinalizedClassDefinition
     */
    private $class;

    /**
     * @var string[]
     */
    private $propertyFieldMap = [];

    /**
     * @var IForm
     */
    private $form;

    /**
     * @var InnerFormDefinition[]
     */
    private $propertyInnerFormMap;

    /**
     * FinalizedFormObjectDefinition constructor.
     *
     * @param FinalizedClassDefinition $class
     * @param string[]                 $propertyFieldMap
     * @param InnerFormDefinition[]    $propertyInnerFormMap
     * @param IForm                    $form
     */
    public function __construct(
            FinalizedClassDefinition $class,
            array $propertyFieldMap,
            array $propertyInnerFormMap,
            IForm $form
    ) {
        $this->class                = $class;
        $this->propertyFieldMap     = $propertyFieldMap;
        $this->propertyInnerFormMap = $propertyInnerFormMap;
        $this->form                 = $form;

        $formInstance = $this->class->getCleanInstance();
        if ($formInstance instanceof FormObject) {
            $formInstance->loadFormObjectDefinition($this);
        }
    }

    /**
     * @return FinalizedClassDefinition
     */
    public function getClass() : FinalizedClassDefinition
    {
        return $this->class;
    }

    /**
     * @return IForm
     */
    public function getForm() : IForm
    {
        return $this->form;
    }

    /**
     * @return string[]
     */
    public function getPropertyFieldMap() : array
    {
        return $this->propertyFieldMap;
    }

    /**
     * @return InnerFormDefinition[]
     */
    public function getPropertyInnerFormMap() : array
    {
        return $this->propertyInnerFormMap;
    }

    /**
     * @param array $initialProcessedValues
     *
     * @return static
     */
    public function withInitialValues(array $initialProcessedValues)
    {
        $clone = clone $this;

        $clone->form = $clone->form->withInitialValues($initialProcessedValues);

        return $clone;
    }

    /**
     * @param string[] $fieldNameMap
     *
     * @return static
     */
    public function withFieldNames(array $fieldNameMap)
    {
        $clone = clone $this;

        $clone->form = $clone->form->withFieldNames($fieldNameMap);

        foreach ($clone->propertyFieldMap as $property => $fieldName) {
            if (isset($fieldNameMap[$fieldName])) {
                $clone->propertyFieldMap[$property] = $fieldNameMap[$fieldName];
            }
        }

        return $clone;
    }
}