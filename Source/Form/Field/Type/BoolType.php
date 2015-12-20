<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\BoolProcessor;
use Dms\Core\Form\Field\Processor\Validator\BoolValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The bool type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolType extends ScalarType
{
    public function __construct()
    {
        parent::__construct(self::BOOL);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new BoolValidator(Type::mixed()),
                new BoolProcessor(),
        ];
    }
}