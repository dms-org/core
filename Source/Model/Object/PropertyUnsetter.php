<?php

namespace Dms\Core\Model\Object;

/**
 * The property unsetter class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyUnsetter
{
    /**
     * @var string[][]
     */
    private $propertiesGroupedByClass;

    /**
     * @var callable[]
     */
    private $unsetters = [];

    /**
     * PropertyUnsetter constructor.
     *
     * @param \string[][] $propertiesGroupedByClass
     */
    public function __construct(array $propertiesGroupedByClass)
    {
        $this->propertiesGroupedByClass = $propertiesGroupedByClass;

        foreach ($this->propertiesGroupedByClass as $class => $properties) {
            $this->unsetters[] = \Closure::bind(function (TypedObject $object) use ($properties) {
                foreach ($properties as $property) {
                    unset($object->{$property});
                }
            }, null, $class);
        }
    }

    public function unsetProperties(TypedObject $object)
    {
        foreach ($this->unsetters as $unsetter) {
            $unsetter($object);
        }
    }
}