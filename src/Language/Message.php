<?php declare(strict_types = 1);

namespace Dms\Core\Language;

/**
 * The message class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Message
{
    const NAMESPACE_SEPARATOR = '::';

    /**
     * @var string|null
     */
    private $namespace;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string[];
     */
    private $parameters;

    /**
     * Message constructor.
     *
     * @param string $id
     * @param array  $parameters
     */
    public function __construct(string $id, array $parameters = [])
    {
        if (strpos($id, self::NAMESPACE_SEPARATOR) !== false) {
            list($this->namespace, $this->id) = explode(self::NAMESPACE_SEPARATOR, $id, 2);
        } else {
            $this->id = $id;
        }

        $this->parameters = $parameters;
    }

    /**
     * @return bool
     */
    public function hasNamespace() : bool
    {
        return $this->namespace !== null;
    }

    /**
     * @return string|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullId() : string
    {
        return ($this->namespace !== null ? $this->namespace . self::NAMESPACE_SEPARATOR : '') . $this->id;
    }

    /**
     * @return string[]
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * @param string[] $parameters
     *
     * @return Message
     */
    public function withParameters(array $parameters) : Message
    {
        return new self($this->getFullId(), $parameters + $this->parameters);
    }
}