<?php declare(strict_types = 1);

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
    public function __construct(string $name, string $label)
    {
        $this->name  = $name;
        $this->label = $label;
    }

    /**
     * @return string
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    final public function getLabel() : string
    {
        return $this->label;
    }
}