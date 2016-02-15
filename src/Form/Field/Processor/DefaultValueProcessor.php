<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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
     * @throws \Dms\Core\Exception\InvalidArgumentException
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