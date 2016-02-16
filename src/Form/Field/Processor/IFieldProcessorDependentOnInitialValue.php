<?php

namespace Dms\Core\Form\Field\Processor;

/**
 * The interface which signifies the processor is dependent
 * on the the fields initial value.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldProcessorDependentOnInitialValue
{
    /**
     * Returns an equivalent processor with the updated initial value
     *
     * @param mixed $initialValue
     *
     * @return static
     */
    public function withInitialValue($initialValue);
}