<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\Builder\Type;

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
    public function __construct()
    {
        parent::__construct(Type::string()->nullable());
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