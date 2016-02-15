<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Orm;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The entity mapper definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityMapperDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * EntityMapperDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Sets the type of the entity mapper. If a callback
     * is passed it will be called to get the entity mapper.
     *
     * @param string|callable $entityMapperTypeOrFactory
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function from($entityMapperTypeOrFactory)
    {
        if (is_callable($entityMapperTypeOrFactory)) {
            call_user_func($this->callback, $entityMapperTypeOrFactory);
        } elseif (is_a($entityMapperTypeOrFactory, IEntityMapper::class, true)) {
            call_user_func($this->callback, function (IOrm $orm) use ($entityMapperTypeOrFactory) {
                return new $entityMapperTypeOrFactory($orm);
            });
        } else {
            throw InvalidArgumentException::format(
                    'Invalid entity object factory: expecting callable or type of %s, %s given',
                    IEntityMapper::class, $entityMapperTypeOrFactory
            );
        }
    }
}