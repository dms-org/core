<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Iddigital\Cms\Core\Persistence\Db\Schema\Database;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The custom orm base class.
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
     * Constructs an orm instances from the supplied data.
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
     * @param array $entityMappersMap
     * @param array $valueObjectMapperMap
     * @param IOrm[] $includedOrms
     *
     * @return CustomOrm
     */
    public static function from(array $entityMappersMap, array $valueObjectMapperMap = [], array $includedOrms = [])
    {
        return new self(function (OrmDefinition $orm) use ($entityMappersMap, $valueObjectMapperMap, $includedOrms) {
            foreach ($entityMappersMap as $entity => $mapper) {
                $orm->entity($entity)->from($mapper);
            }

            foreach ($valueObjectMapperMap as $valueObject => $mapper) {
                $orm->valueObject($valueObject)->from($mapper);
            }

            foreach ($includedOrms as $includedOrm) {
                $orm->encompass($includedOrm);
            }
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