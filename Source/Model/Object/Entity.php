<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Util\Hashing\IHashable;

/**
 * The entity object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Entity extends TypedObject implements IEntity, IHashable
{
    /**
     * @var int
     */
    private $id;

    /**
     * Entity constructor.
     *
     * @param int|null $id
     */
    public function __construct($id = null)
    {
        parent::__construct();
        $this->id = $id;
    }

    /**
     * Returns an entity collection with the element type
     * as the called class.
     *
     * @param static[] $entities
     *
     * @return EntityCollection|static[]
     */
    final public static function collection(array $entities = [])
    {
        return new EntityCollection(get_called_class(), $entities);
    }

    /**
     * Returns the type of the collection for this typed object.
     *
     * @return IType
     */
    final public static function collectionType()
    {
        return Type::collectionOf(Type::object(get_called_class()), EntityCollection::class);
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    final protected function define(ClassDefinition $class)
    {
        $class->property($this->id)->nullable()->asInt();

        $this->defineEntity($class);
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    abstract protected function defineEntity(ClassDefinition $class);

    /**
     * {@inheritDoc}
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    final public function hasId()
    {
        return $this->id !== null;
    }

    /**
     * {@inheritDoc}
     */
    final public function setId($id)
    {
        if ($this->id !== null) {
            throw Exception\InvalidOperationException::methodCall(__METHOD__, 'the id has already been set');
        }

        Exception\InvalidArgumentException::verifyNotNull(__METHOD__, 'id', $id);

        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    final public function getObjectHash()
    {
        return $this->id === null
                ? serialize($this->toArray())
                : (string)$this->id;
    }
}