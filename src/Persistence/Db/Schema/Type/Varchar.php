<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

/**
 * The db varchar type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Varchar extends Type
{
    /**
     * @var int
     */
    private $length;

    /**
     * Varchar constructor.
     *
     * @param int $length
     */
    public function __construct(int $length)
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getLength() : int
    {
        return $this->length;
    }
}