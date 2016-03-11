<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Object\FormObject;
use Dms\Core\Form\Object\Processor\InnerFormObjectProcessor;

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
    public function getFormObject() : FormObject
    {
        return $this->attributes[self::ATTR_FORM_OBJECT];
    }

    /**
     * @inheritDoc
     */
    protected function initializeFromCurrentAttributes()
    {
        parent::initializeFromCurrentAttributes();

        if ($this->get(self::ATTR_INITIAL_VALUE) instanceof FormObject) {
            $this->attributes[self::ATTR_FORM_OBJECT] = $this->get(self::ATTR_INITIAL_VALUE);
        }
    }


    /**
     * @inheritDoc
     */
    protected function buildProcessors() : array
    {
        return [
            new InnerFormObjectProcessor($this->getFormObject())
        ];
    }


    /**
     * @return mixed|null
     */
    protected function getInitialValuesForInnerForm()
    {
        $value = $this->get(self::ATTR_INITIAL_VALUE);

        if ($value instanceof FormObject) {
            return $value->getInitialValues();
        }

        return null;
    }
}