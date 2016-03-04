<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\CollectionType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Util\Hashing\IHashable;

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
    public static function collection(array $entities = [])
    {
        return new EntityCollection(get_called_class(), $entities);
    }

    /**
     * Returns the type of the collection for this typed object.
     *
     * @return CollectionType
     */
    public static function collectionType() : CollectionType
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
    final public function hasId() : bool
    {
        return $this->id !== null;
    }

    /**
     * {@inheritDoc}
     */
    final public function setId(int $id)
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
    public function getObjectHash() : string
    {
        return $this->id === null
                ? serialize($this->toArray())
                : (string)$this->id;
    }
}