<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\FloatValidator;
use Dms\Core\Form\IFieldProcessor;

/**
 * The float type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FloatType extends ScalarType
{
    const ATTR_MIN = 'min';
    const ATTR_MAX = 'max';
    const ATTR_MIN_DECIMAL_POINTS = 'min-decimal-points';
    const ATTR_MAX_DECIMAL_POINTS = 'max-decimal-points';

    public function __construct()
    {
        parent::__construct(self::FLOAT);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new FloatValidator($this->inputType),
                new TypeProcessor('float'),
        ];
    }
}