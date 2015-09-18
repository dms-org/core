<?php

namespace Iddigital\Cms\Core\Form\Object;

use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;

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
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return IForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return string[]
     */
    public function getPropertyFieldMap()
    {
        return $this->propertyFieldMap;
    }

    /**
     * @return InnerFormDefinition[]
     */
    public function getPropertyInnerFormMap()
    {
        return $this->propertyInnerFormMap;
    }
}