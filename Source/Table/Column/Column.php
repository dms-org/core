<?php

namespace Iddigital\Cms\Core\Table\Column;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The column class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Column implements IColumn
{
    /**
     * @var string
     */
    protected static $debugType = 'column';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var IColumnComponent[]
     */
    protected $components = [];

    /**
     * Column constructor.
     *
     * @param string             $name
     * @param string             $label
     * @param IColumnComponent[] $components
     */
    public function __construct($name, $label, array $components)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'components', $components, IColumnComponent::class);

        $this->name  = $name;
        $this->label = $label;
        foreach ($components as $component) {
            $this->components[$component->getName()] = $component;
        }
    }

    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    final public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return IColumnComponent[]
     */
    final public function getComponents()
    {
        return $this->components;
    }

    /**
     * @return string[]
     */
    final public function getComponentNames()
    {
        return array_keys($this->components);
    }

    /**
     * @inheritDoc
     */
    public function hasSingleComponent()
    {
        return count($this->components) === 1;
    }

    /**
     * @param string $componentName
     *
     * @return IColumnComponent
     * @throws bool
     */
    final public function hasComponent($componentName)
    {
        return isset($this->components[$componentName]);
    }

    /**
     * @param string $componentName
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    final public function getComponent($componentName = null)
    {
        if ($componentName === null) {
            if ($this->hasSingleComponent()) {
                return reset($this->components);
            } else {
                throw InvalidArgumentException::format(
                        'Must supply component name for %s \'%s\' with more than one component: expecting one of (%s), null given',
                        static::$debugType, $this->name, Debug::formatValues(array_keys($this->components))
                );
            }
        }

        if (!isset($this->components[$componentName])) {
            throw InvalidArgumentException::format(
                    'Invalid component name for %s \'%s\': expecting one of (%s), %s given',
                    static::$debugType, $this->name, Debug::formatValues(array_keys($this->components)), $componentName
            );
        }

        return $this->components[$componentName];
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentId($componentName = null)
    {
        return $this->name . '.' . $this->getComponent($componentName)->getName();
    }
}