<?php

namespace Dms\Core\Table\Column\Component;

use Dms\Core\Form\IField;
use Dms\Core\Table\Column\Component\Type\ColumnComponentType;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\IColumnComponentType;

/**
 * The column component base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponent implements IColumnComponent
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
     * @var IColumnComponentType
     */
    protected $type;

    /**
     * ColumnComponent constructor.
     *
     * @param string               $name
     * @param string               $label
     * @param IColumnComponentType $type
     */
    public function __construct($name, $label, IColumnComponentType $type)
    {
        $this->name  = $name;
        $this->label = $label;
        $this->type  = $type->withFieldAs($name, $label);
    }

    /**
     * @param IField $field
     *
     * @return ColumnComponent
     */
    public static function forField(IField $field)
    {
        return new self($field->getName(), $field->getLabel(), ColumnComponentType::forField($field));
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    final public function getType()
    {
        return $this->type;
    }
}