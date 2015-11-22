<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hook;

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
    public function __construct($idString)
    {
        $this->idString = $idString;
    }

    /**
     * @return string
     */
    final public function getIdString()
    {
        return $this->idString;
    }
}