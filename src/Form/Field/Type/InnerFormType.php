<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\InnerFormProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Form\IForm;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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
    public function getForm() : IForm
    {
        return $this->get(self::ATTR_FORM);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildPhpTypeOfInput() : IType
    {
        return Type::arrayOf(Type::mixed());
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        return [
            new InnerFormProcessor($this->getForm()),
        ];
    }

    /**
     * @param string $arrayFieldName
     *
     * @return IForm
     */
    public function getInnerArrayForm(string $arrayFieldName) : IForm
    {
        $form = $this->getForm();

        $currentValue = $this->getInitialValuesForInnerForm();

        if (is_array($currentValue)) {
            $form = $form->withInitialValues($currentValue);
        }

        $fieldNameMap = [];

        foreach ($form->getFieldNames() as $fieldName) {
            $fieldNameMap[$fieldName] = $arrayFieldName . '[' . $fieldName . ']';
        }

        return $form->withFieldNames($fieldNameMap);
    }

    /**
     * @return mixed|null
     */
    protected function getInitialValuesForInnerForm()
    {
        return $this->get(self::ATTR_INITIAL_VALUE);
    }
}