<?php

namespace Iddigital\Cms\Core\Table\Chart\Structure;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Table\Chart\IChartAxis;
use Iddigital\Cms\Core\Table\Column\Column;
use Iddigital\Cms\Core\Table\Column\Component\ColumnComponent;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\IColumnComponentType;

/**
 * The chart axis class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartAxis extends Column implements IChartAxis
{
    /**
     * @var string
     */
    protected static $debugType = 'chart axis';

    /**
     * @var IColumnComponentType
     */
    private $type;

    /**
     * @param string               $name
     * @param string               $label
     * @param IColumnComponent[]   $components
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name, $label, array $components)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'components', $components, IColumnComponent::class);
        InvalidArgumentException::verify(!empty($components), 'Components cannot be empty');

        /** @var IColumnComponent $firstComponent */
        $firstComponent = reset($components);
        $this->type = $firstComponent->getType();

        foreach ($components as $component) {
            if (!$component->getType()->equals($this->type)) {
                throw InvalidArgumentException::format(
                        'Invalid component supplied to chart axis \'%s\': expecting component type %s, %s given for component \'%s\'',
                        $name, $this->type->getPhpType()->asTypeString(), $component->getType()->getPhpType()->asTypeString(),
                        $component->getName()
                );
            }

            $this->components[$component->getName()] = $component;
        }

        parent::__construct($name, $label, $components);
    }

    /**
     * @param IField $field
     *
     * @return ChartAxis
     */
    public static function forField(IField $field)
    {
        return self::fromComponent(ColumnComponent::forField($field));
    }

    /**
     * @param IColumnComponent $component
     *
     * @return ChartAxis
     */
    public static function fromComponent(IColumnComponent $component)
    {
        return new self($component->getName(), $component->getLabel(), [$component]);
    }

    /**
     * @return IColumnComponentType
     */
    public function getType()
    {
        return $this->type;
    }
}