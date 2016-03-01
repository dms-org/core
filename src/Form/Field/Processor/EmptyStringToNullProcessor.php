<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\IType;

/**
 * The empty string as null process processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmptyStringToNullProcessor extends FieldProcessor
{
    /**
     * @inheritDoc
     */
    public function __construct(IType $inputType)
    {
        parent::__construct($inputType);
    }

    protected function doProcess($input, array &$messages)
    {
        return $input === '' ? null : $input;
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}