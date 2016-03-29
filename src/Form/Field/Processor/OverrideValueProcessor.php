<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The override supplied validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OverrideValueProcessor extends FieldProcessor
{
    /**
     * @var
     */
    private $value;

    /**
     * @inheritDoc
     */
    public function __construct(IType $processedType, $value)
    {
        parent::__construct($processedType);
        $this->value = $value;
    }


    /**
     * @param mixed $input
     * @param array $messages
     *
     * @return mixed
     */
    protected function doProcess($input, array &$messages)
    {
        return $this->value;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    protected function doUnprocess($input)
    {
        return $this->value;
    }
}