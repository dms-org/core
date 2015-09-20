<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Orm;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The orm definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrmDefinition
{
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
     * Registers a mapper for the supplied entity class.
     *
     * @param string $entityClass
     *
     * @return EntityMapperDefiner
     */
    public function entity($entityClass)
    {
        return new EntityMapperDefiner(function (callable $entityMapperFactory) use ($entityClass) {
            $this->entityMapperFactories[] = $entityMapperFactory;
        });
    }

    /**
     * Registers a mapper for the supplied value object class.
     *
     * @param string $valueObjectClass
     *
     * @return EmbeddedMapperDefiner
     */
    public function valueObject($valueObjectClass)
    {
        return new EmbeddedMapperDefiner(function (callable $embeddedMapperFactory) use ($valueObjectClass) {
            $this->embeddedObjectMapperFactories[$valueObjectClass] = $embeddedMapperFactory;
        });
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
     * @param callable $loaderCallback
     *
     * @return void
     */
    public function finalize(callable $loaderCallback)
    {
        $loaderCallback($this->entityMapperFactories, $this->embeddedObjectMapperFactories, $this->includedOrms);
    }
}