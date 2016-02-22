<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition;

use App\Dms\Demo\DemoEntity;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The label object strategy definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LabelObjectStrategyDefiner
{
    /**
     * @var string
     */
    private $classType;

    /**
     * @var callable
     */
    private $callback;

    /**
     * LabelObjectStrategyDefiner constructor.
     *
     * @param string   $classType
     * @param callable $callback
     */
    public function __construct(string $classType, callable $callback)
    {
        $this->classType = $classType;
        $this->callback  = $callback;
    }

    /**
     * Labels the object with the value of the supplied property.
     *
     * @param string $propertyName
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function fromProperty(string $propertyName)
    {
        $classType = $this->classType;
        /** @var FinalizedClassDefinition $definition */
        $definition = $classType::definition();
        if (!$definition->hasProperty($propertyName)) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: property %s::$%s does not exist',
                    __METHOD__, $this->classType, $propertyName
            );
        }

        $this->fromCallback(function (ITypedObject $object) use ($propertyName) {
            return $object->toArray()[$propertyName];
        });
    }

    /**
     * Labels the object with the returned value of the supplied callback.
     *
     * Example:
     * <code>
     * ->fromCallback(function (Person $person) {
     *      return $person->getFullName();
     * });
     * </code>
     *
     * @param callable $labelObjectCallback
     *
     * @return void
     */
    public function fromCallback(callable $labelObjectCallback)
    {
        call_user_func($this->callback, $labelObjectCallback);
    }
}