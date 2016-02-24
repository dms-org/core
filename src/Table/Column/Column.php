<?php declare(strict_types = 1);

namespace Dms\Core\Table\Column;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Util\Debug;

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
     * @var bool
     */
    protected $hidden;

    /**
     * @var IColumnComponent[]
     */
    protected $components = [];

    /**
     * Column constructor.
     *
     * @param string             $name
     * @param string             $label
     * @param bool               $hidden
     * @param IColumnComponent[] $components
     */
    public function __construct(string $name, string $label, bool $hidden, array $components)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'components', $components, IColumnComponent::class);

        $this->name   = $name;
        $this->label  = $label;
        $this->hidden = $hidden;
        foreach ($components as $component) {
            $this->components[$component->getName()] = $component;
        }
    }

    /**
     * @return string
     */
    final public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    final public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    final public function isHidden() : bool
    {
        return $this->hidden;
    }

    /**
     * @return IColumnComponent[]
     */
    final public function getComponents() : array
    {
        return $this->components;
    }

    /**
     * @return string[]
     */
    final public function getComponentNames() : array
    {
        return array_keys($this->components);
    }

    /**
     * @inheritDoc
     */
    public function hasSingleComponent() : bool
    {
        return count($this->components) === 1;
    }

    /**
     * @param string $componentName
     *
     * @return bool
     */
    final public function hasComponent(string $componentName) : bool
    {
        return isset($this->components[$componentName]);
    }

    /**
     * @param string $componentName
     *
     * @return IColumnComponent
     * @throws InvalidArgumentException
     */
    final public function getComponent(string $componentName = null) : IColumnComponent
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
    public function getComponentId(string $componentName = null) : string
    {
        return $this->name . '.' . $this->getComponent($componentName)->getName();
    }
}