<?php

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Form\IFieldOption;

/**
 * The field option class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldOption implements IFieldOption
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $label;

    public function __construct($value, $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * {@inheritDoc]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritDoc]
     */
    public function getLabel()
    {
        return $this->label;
    }
}