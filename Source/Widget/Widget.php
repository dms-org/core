<?php

namespace Dms\Core\Widget;

/**
 * The widget base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Widget implements IWidget
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * Widget constructor.
     *
     * @param string $name
     * @param string $label
     */
    public function __construct($name, $label)
    {
        $this->name  = $name;
        $this->label = $label;
    }

    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    final public function getLabel()
    {
        return $this->label;
    }
}