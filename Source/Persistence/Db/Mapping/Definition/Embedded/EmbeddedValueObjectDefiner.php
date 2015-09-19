<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded;

use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomValueObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;

/**
 * The embedded value object definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedValueObjectDefiner
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $issetColumnName;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Sets the embedded object columns to be prefixed
     * by the supplied string.
     *
     * @param string $prefix
     *
     * @return static
     */
    public function withColumnsPrefixedBy($prefix)
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
    public function withIssetColumn($columnName)
    {
        $this->issetColumnName = $columnName;

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
        call_user_func($this->callback, $this->prefix ? $mapper->withColumnsPrefixedBy($this->prefix) : $mapper, $this->issetColumnName);
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
        $this->using(new CustomValueObjectMapper($mapperDefinitionCallback));
    }
}