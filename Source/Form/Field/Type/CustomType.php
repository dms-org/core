<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\IType;

/**
 * The custom type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomType extends FieldType
{
    /**
     * @var IFieldProcessor[]
     */
    protected $customProcessors;

    /**
     * CustomType constructor.
     *
     * @param IType $inputType
     * @param array $processors
     */
    public function __construct(IType $inputType, array $processors)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'processors', $processors, IFieldProcessor::class);

        $this->inputType        = $inputType;
        $this->customProcessors = $processors;
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
        return $this->customProcessors;
    }
}