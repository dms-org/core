<?php declare(strict_types = 1);

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
    /**
     * @var bool
     */
    private $disabled;

    /**
     * FieldOption constructor.
     *
     * @param mixed  $value
     * @param string $label
     * @param bool   $disabled
     */
    public function __construct($value, string $label, bool $disabled = false)
    {
        $this->value = $value;
        $this->label = $label;
        $this->disabled = $disabled;
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
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return boolean
     */
    public function isDisabled() : bool
    {
        return $this->disabled;
    }
}