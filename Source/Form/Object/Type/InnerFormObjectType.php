<?php

namespace Dms\Core\Form\Object\Type;

use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\Object\FormObject;
use Dms\Core\Form\Object\Processor\InnerFormObjectProcessor;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\IType as IPhpType;
use Dms\Core\Model\Type\MixedType;

/**
 * The inner form object type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormObjectType extends InnerFormType
{
    const ATTR_FORM_OBJECT = 'form-object';

    /**
     * FormObjectType constructor.
     *
     * @param FormObject $formObject
     *
     * @throws mixed
     * @internal param string $formObjectType
     */
    public function __construct(FormObject $formObject)
    {
        $this->attributes[self::ATTR_FORM_OBJECT] = $formObject;
        parent::__construct($formObject->getForm());
    }

    /**
     * @return FormObject
     */
    public function getFormObject()
    {
        return $this->attributes[self::ATTR_FORM_OBJECT];
    }

    /**
     * @return IPhpType
     */
    protected function buildPhpTypeOfInput()
    {
        return new ArrayType(new MixedType());
    }

    /**
     * @inheritDoc
     */
    protected function buildProcessors()
    {
        return [
                new InnerFormObjectProcessor($this->getFormObject())
        ];
    }


}