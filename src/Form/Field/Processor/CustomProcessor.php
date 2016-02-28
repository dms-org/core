<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\IType;

/**
 * The custom processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomProcessor extends FieldProcessor
{
    /**
     * @var callable
     */
    private $processCallback;

    /**
     * @var callable
     */
    private $unprocessCallback;

    public function __construct(IType $processedType, callable $processCallback, callable $unprocessCallback)
    {
        parent::__construct($processedType);
        $this->processCallback   = $processCallback;
        $this->unprocessCallback = $unprocessCallback;
    }

    protected function doProcess($input, array &$messages)
    {
        return call_user_func_array($this->processCallback, [$input, &$messages]);
    }

    protected function doUnprocess($input)
    {
        return call_user_func($this->unprocessCallback, $input);
    }
}