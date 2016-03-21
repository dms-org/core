<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Orm;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The embedded mapper definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedMapperDefiner
{
    /**
     * @var IIocContainer
     */
    private $iocContainer;

    /**
     * @var callable
     */
    private $callback;

    /**
     * EmbeddedMapperDefiner constructor.
     *
     * @param IIocContainer $iocContainer
     * @param callable      $callback
     */
    public function __construct(IIocContainer $iocContainer = null, callable $callback)
    {
        $this->iocContainer = $iocContainer;
        $this->callback     = $callback;
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
        } elseif (is_a($embeddedObjectMapperTypeOrFactory, IndependentValueObjectMapper::class, true)) {
            call_user_func($this->callback, function () use ($embeddedObjectMapperTypeOrFactory) {
                return $this->iocContainer ? $this->iocContainer->get($embeddedObjectMapperTypeOrFactory) : new $embeddedObjectMapperTypeOrFactory();
            });
        } elseif (is_a($embeddedObjectMapperTypeOrFactory, IEmbeddedObjectMapper::class, true)) {
            call_user_func($this->callback, function (IOrm $orm, IObjectMapper $parentMapper) use ($embeddedObjectMapperTypeOrFactory) {

                if ($this->iocContainer) {

                    return $this->iocContainer->bindForCallback(IOrm::class, $orm, function () use ($parentMapper, $embeddedObjectMapperTypeOrFactory) {

                        return $this->iocContainer->bindForCallback(IObjectMapper::class, $parentMapper, function () use ($embeddedObjectMapperTypeOrFactory) {
                            return  $this->iocContainer->get($embeddedObjectMapperTypeOrFactory);
                        });

                    });
                }

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