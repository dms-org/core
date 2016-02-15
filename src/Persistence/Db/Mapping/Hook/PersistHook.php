<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hook;

/**
 * The persist hook base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class PersistHook implements IPersistHook
{
    /**
     * @var string
     */
    protected $idString;

    /**
     * PersistHook constructor.
     *
     * @param string $idString
     */
    public function __construct(string $idString)
    {
        $this->idString = $idString;
    }

    /**
     * @return string
     */
    final public function getIdString() : string
    {
        return $this->idString;
    }
}