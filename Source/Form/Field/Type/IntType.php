<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\IntValidator;
use Iddigital\Cms\Core\Form\IFieldProcessor;

/**
 * The integer type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntType extends ScalarType
{
    const ATTR_MIN = 'min';
    const ATTR_MAX = 'max';

    public function __construct()
    {
        parent::__construct(self::INT);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new IntValidator($this->inputType),
                new TypeProcessor('int'),
        ];
    }
}