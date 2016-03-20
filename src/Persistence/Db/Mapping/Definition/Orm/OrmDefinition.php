<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Orm;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The orm definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrmDefinition
{
    /**
     * @var IIocContainer|null
     */
    protected $iocContainer;

    /**
     * @var callable[]
     */
    protected $entityMapperFactories = [];

    /**
     * @var callable[]
     */
    protected $embeddedObjectMapperFactories = [];

    /**
     * @var IOrm[]
     */
    protected $includedOrms = [];

    /**
     * OrmDefinition constructor.
     *
     * @param IIocContainer|null $iocContainer
     */
    public function __construct(IIocContainer $iocContainer = null)
    {
        $this->iocContainer = $iocContainer;
    }

    /**
     * Registers a mapper for the supplied entity class.
     *
     * @param string $entityClass
     *
     * @return EntityMapperDefiner
     */
    public function entity(string $entityClass) : EntityMapperDefiner
    {
        return new EntityMapperDefiner($this->iocContainer, function (callable $entityMapperFactory) use ($entityClass) {
            $this->entityMapperFactories[] = $entityMapperFactory;
        });
    }

    /**
     * Registers an array of entity mappers.
     *
     * Example:
     * <code>
     * $orm->entities([
     *      SomeEntity::class => SomeEntityMapper::class,
     * ]);
     * </code>
     *
     * @param string[]|callable[] $entityClassMapperClassMap
     *
     * @return void
     */
    public function entities($entityClassMapperClassMap)
    {
        foreach ($entityClassMapperClassMap as $entity => $mapper) {
            $this->entity($entity)->from($mapper);
        }
    }

    /**
     * Registers a mapper for the supplied value object class.
     *
     * @param string $valueObjectClass
     *
     * @return EmbeddedMapperDefiner
     */
    public function valueObject(string $valueObjectClass) : EmbeddedMapperDefiner
    {
        return new EmbeddedMapperDefiner($this->iocContainer, function (callable $embeddedMapperFactory) use ($valueObjectClass) {
            $this->embeddedObjectMapperFactories[$valueObjectClass] = $embeddedMapperFactory;
        });
    }

    /**
     * Registers an array of value object mappers.
     *
     * Example:
     * <code>
     * $orm->valueObjects([
     *      SomeValueObject::class => SomeValueObjectMapper::class,
     * ]);
     * </code>
     *
     * @param string[]|callable[] $valueObjectClassMapperClassMap
     *
     * @return void
     */
    public function valueObjects($valueObjectClassMapperClassMap)
    {
        foreach ($valueObjectClassMapperClassMap as $valueObject => $mapper) {
            $this->valueObject($valueObject)->from($mapper);
        }
    }

    /**
     * Registers another orm instance to include the entity
     * and value object mappers from.
     *
     * @param IOrm $orm
     *
     * @return void
     */
    public function encompass(IOrm $orm)
    {
        $this->includedOrms[] = $orm;
    }

    /**
     * Registers an array of other orm instances to include the entity
     * and value object mappers from.
     *
     * @param IOrm[] $orms
     *
     * @return void
     */
    public function encompassAll(array $orms)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'orms', $orms, IOrm::class);

        $this->includedOrms = array_merge($this->includedOrms, $orms);
    }

    /**
     * @param callable $loaderCallback
     *
     * @return void
     */
    public function finalize(callable $loaderCallback)
    {
        $loaderCallback($this->entityMapperFactories, $this->embeddedObjectMapperFactories, $this->includedOrms);
    }
}