<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The default value type processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultValueProcessor extends FieldProcessor
{
    /**
     * @var mixed
     */
    private $default;

    /**
     * @param IType $processedType
     * @param mixed $default
     *
     * @throws \Iddigital\Cms\Core\Exception\InvalidArgumentException
     */
    public function __construct(IType $processedType, $default)
    {
        parent::__construct($processedType);

        $this->default       = $default;
        $this->processedType = $processedType->nonNullable()->union(Type::from($default));
    }

    public function process($input, array &$messages)
    {
        if ($input === null) {
            return $this->default;
        } else {
            return parent::process($input, $messages);
        }
    }

    protected function doProcess($input, array &$messages)
    {
        return $input;
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}