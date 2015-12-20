<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The custom type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomType extends FieldType
{
    /**
     * CustomType constructor.
     *
     * @param IType $inputType
     * @param array $processors
     */
    public function __construct(IType $inputType, array $processors)
    {
        $this->inputType = $inputType;
        $this->processors = $processors;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function buildPhpTypeOfInput()
    {
        return $this->inputType;
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return $this->processors;
    }
}