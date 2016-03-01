<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\IType;

/**
 * The array null filler processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayFillWithNullsProcessor extends FieldProcessor
{
    /**
     * @var int
     */
    private $keysToFillWithNulls;

    public function __construct(IType $processedType, array $keys)
    {
        parent::__construct($processedType);
        $this->keysToFillWithNulls = array_fill_keys($keys, null);
    }

    /**
     * @param mixed $input
     * @param array $messages
     *
     * @return mixed
     */
    protected function doProcess($input, array &$messages)
    {
        return $input + $this->keysToFillWithNulls;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    protected function doUnprocess($input)
    {
        return $input;
    }
}