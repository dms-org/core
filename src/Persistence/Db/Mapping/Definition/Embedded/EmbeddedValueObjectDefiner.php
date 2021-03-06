<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Embedded;

use Dms\Core\Persistence\Db\Mapping\CustomValueObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;

/**
 * The embedded value object definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedValueObjectDefiner extends EmbeddedRelationDefiner
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $issetColumnName;

    /**
     * @var bool
     */
    protected $isUnique = false;

    /**
     * Sets the embedded object columns to be prefixed
     * by the supplied string.
     *
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnsPrefixedBy(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Sets the column on the parent to determine whether
     * the embedded value object is set or null.
     *
     * @param string $columnName
     *
     * @return static
     */
    public function withIssetColumn(string $columnName)
    {
        $this->issetColumnName = $columnName;

        return $this;
    }

    /**
     * Defines a unique constraint across all the columns from the
     * embedded value object mapper.
     *
     * @return static
     */
    public function unique()
    {
        $this->isUnique = true;

        return $this;
    }

    /**
     * Sets the mapper to use to embed the value object
     * in the parent class mapping.
     *
     * @param IEmbeddedObjectMapper $mapper
     *
     * @return void
     */
    public function using(IEmbeddedObjectMapper $mapper)
    {
        $this->defineRelation(function () use ($mapper) {
            return $mapper;
        });
    }

    /**
     * Defines the embedded object mapper using the supplied callback
     *
     * @param callable $mapperDefinitionCallback
     *
     * @return void
     */
    public function usingCustom(callable $mapperDefinitionCallback)
    {
        $this->defineRelation(function (IObjectMapper $parentMapper) use ($mapperDefinitionCallback) {
            return new CustomValueObjectMapper($this->orm, $parentMapper, $mapperDefinitionCallback);
        });
    }

    /**
     * Sets the type of value object to use to embed
     * in the parent class mapping.
     *
     * @param string $valueObjectClass
     *
     * @return void
     */
    public function to(string $valueObjectClass)
    {
        $this->defineRelation(function (IObjectMapper $parentObjectMapper) use ($valueObjectClass) {
            return $this->orm->loadEmbeddedObjectMapper($parentObjectMapper, $valueObjectClass);
        });
    }

    /**
     * Defines the embedded object mapper using the supplied callback
     * 
     * Example:
     * <code>
     * ->usingCallback(function (IOrm $orm, IObjectMapper $mapper) {
     *      return new YourValueObjectMapper($orm, $mapper);
     * });
     * </code>
     *
     * @param callable $mapperLoaderCallback
     *
     * @return void
     */
    public function usingCallback(callable $mapperLoaderCallback)
    {
        $this->defineRelation(function (IObjectMapper $parentMapper) use ($mapperLoaderCallback) {
            return $mapperLoaderCallback($this->orm, $parentMapper);
        });
    }

    /**
     * @param callable $mapperLoader
     *
     * @return void
     */
    protected function defineRelation(callable $mapperLoader)
    {
        call_user_func($this->callback, function (IObjectMapper $parentMapper) use ($mapperLoader) {
            /** @var IEmbeddedObjectMapper $mapper */
            $mapper = $mapperLoader($parentMapper);
            return $this->prefix ? $mapper->withColumnsPrefixedBy($this->prefix) : $mapper;
        }, $this->issetColumnName, $this->isUnique);
    }
}