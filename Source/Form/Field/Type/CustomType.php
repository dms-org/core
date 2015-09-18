<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

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