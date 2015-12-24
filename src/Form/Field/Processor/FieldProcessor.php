<?php

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\IType;

/**
 * The field processor base.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldProcessor implements IFieldProcessor
{
    /**
     * @var IType
     */
    protected $processedType;

    /**
     * FieldProcessor constructor.
     *
     * @param IType $processedType
     */
    public function __construct(IType $processedType)
    {
        $this->processedType = $processedType->nullable();
    }

    /**
     * {@inheritDoc}
     */
    final public function getProcessedType()
    {
        return $this->processedType;
    }

    /**
     * {@inheritDoc}
     */
    public function process($input, array &$messages)
    {
        if ($input === null) {
            return null;
        }

        return $this->doProcess($input, $messages);
    }

    /**
     * {@inheritDoc}
     */
    public function unprocess($input)
    {
        if ($input === null) {
            return null;
        }

        return $this->doUnprocess($input);
    }

    /**
     * @param mixed      $input
     * @param array $messages
     *
     * @return mixed
     */
    abstract protected function doProcess($input, array &$messages);

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    abstract protected function doUnprocess($input);
}