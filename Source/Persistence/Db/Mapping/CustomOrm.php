<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;

/**
 * The custom orm class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomOrm extends Orm
{
    /**
     * @var callable
     */
    private $defineCallback;

    /**
     * CustomOrm constructor.
     *
     * @param callable $defineCallback
     */
    public function __construct(callable $defineCallback)
    {
        $this->defineCallback = $defineCallback;
        parent::__construct();
    }

    /**
     * Constructs an orm instance from the supplied entity / mapper classes.
     *
     * Example:
     * <code>
     * CustomOrm::from(
     *      [SomeEntity::class => SomeEntityMapper::class],
     *      [SomeValueObject::class => SomeValueObjectMapper::class],
     *      [new SomeOtherOrm()],
     * )
     * </code>
     *
     * @param array  $entityMappersMap
     * @param array  $valueObjectMapperMap
     * @param IOrm[] $includedOrms
     *
     * @return CustomOrm
     */
    public static function from(array $entityMappersMap, array $valueObjectMapperMap = [], array $includedOrms = [])
    {
        return new self(function (OrmDefinition $orm) use ($entityMappersMap, $valueObjectMapperMap, $includedOrms) {
            $orm->entities($entityMappersMap);
            $orm->valueObjects($valueObjectMapperMap);
            $orm->encompassAll($includedOrms);
        });
    }

    /**
     * Defines the object mappers registered in the orm.
     *
     * @param OrmDefinition $orm
     *
     * @return void
     */
    protected function define(OrmDefinition $orm)
    {
        call_user_func($this->defineCallback, $orm);
    }
}