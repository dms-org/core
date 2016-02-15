<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

/**
 * The db enum type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Enum extends Type
{
    /**
     * @var string[]
     */
    private $options;

    /**
     * Enum constructor.
     *
     * @param string[] $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return string[]
     */
    public function getOptions() : array
    {
        return $this->options;
    }
}