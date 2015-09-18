<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\Field\Processor\BoolProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\BoolValidator;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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