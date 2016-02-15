<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\Builder\Type;

/**
 * The bool processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolProcessor extends FieldProcessor
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(Type::bool());
    }

    protected function doProcess($input, array &$messages)
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}