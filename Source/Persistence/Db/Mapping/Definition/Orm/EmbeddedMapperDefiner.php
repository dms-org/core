<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Orm;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The embedded mapper definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMapperDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * EmbeddedMapperDefiner constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Sets the type of the embedded object mapper. If a callback
     * is passed it will be called to get the object mapper.
     *
     * @param string|callable $embeddedObjectMapperTypeOrFactory
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function from($embeddedObjectMapperTypeOrFactory)
    {
        if (is_callable($embeddedObjectMapperTypeOrFactory)) {
            call_user_func($this->callback, $embeddedObjectMapperTypeOrFactory);
        } elseif (is_a($embeddedObjectMapperTypeOrFactory, IEmbeddedObjectMapper::class, true)) {
            call_user_func($this->callback, function (IOrm $orm, IObjectMapper $parentMapper) use ($embeddedObjectMapperTypeOrFactory) {
                return new $embeddedObjectMapperTypeOrFactory($orm, $parentMapper);
            });
        } else {
            throw InvalidArgumentException::format(
                    'Invalid embedded object factory: expecting callable or type of %s, %s given',
                    IEmbeddedObjectMapper::class, $embeddedObjectMapperTypeOrFactory
            );
        }
    }
}