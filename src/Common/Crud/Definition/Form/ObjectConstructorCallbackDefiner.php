<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The object constructor callback definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectConstructorCallbackDefiner
{
    /**
     * @var FinalizedClassDefinition
     */
    private $class;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * ObjectConstructorCallbackDefiner constructor.
     *
     * @param FinalizedClassDefinition $class
     * @param callable                 $callback
     */
    public function __construct(FinalizedClassDefinition $class, callable $callback)
    {
        $this->class    = $class;
        $this->callback = $callback;
    }

    /**
     * Defines a callback to create new instances of the object.
     * The callback can either return an instance or the class
     * name of the object of which to construct.
     *
     * This is only called when a create form is submitted.
     *
     * Example:
     * <code>
     * $form->createObjectType()->fromCallback(function (array $input) {
     *      if ($input['type'] === 'new') {
     *          return NewObject::class;
     *          // Or
     *          return new NewObject(...);
     *      }
     *
     *      return DefaultObject::class;
     * });
     * </code>
     *
     * @param callable $classTypeCallback
     *
     * @return void
     */
    public function fromCallback(callable $classTypeCallback)
    {
        call_user_func($this->callback, $classTypeCallback);
    }

    /**
     * Defines the form to create new instances of the supplied type.
     *
     * @param string $className
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function asClass(string $className)
    {
        if (!is_a($className, $this->class->getClassName(), true)) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: class name must be compatible with type %s, %s given',
                    __METHOD__, $this->class->getClassName(), $className
            );
        }

        $this->fromCallback(function () use ($className) {
            return $className;
        });
    }
}