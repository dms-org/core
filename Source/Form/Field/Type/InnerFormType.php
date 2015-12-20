<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\InnerFormProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IForm;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The inner form type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormType extends FieldType
{
    const ATTR_FORM = 'form';

    public function __construct(IForm $form)
    {
        $this->attributes[self::ATTR_FORM] = $form;
        parent::__construct();
    }

    /**
     * @return IForm
     */
    public function getForm()
    {
        return $this->get(self::ATTR_FORM);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildPhpTypeOfInput()
    {
        return Type::arrayOf(Type::mixed());
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new InnerFormProcessor($this->getForm())
        ];
    }
}