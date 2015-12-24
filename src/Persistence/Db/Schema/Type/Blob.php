<?php

namespace Dms\Core\Persistence\Db\Schema\Type;

/**
 * The db blob type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Blob extends Type
{
    const MODE_SMALL = 'small';
    const MODE_NORMAL = 'normal';
    const MODE_MEDIUM = 'medium';
    const MODE_LONG = 'long';

    /**
     * @var string
     */
    private $mode;

    /**
     * Blob constructor.
     *
     * @param string $mode
     */
    private function __construct($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return Blob
     */
    public static function small()
    {
        return new self(self::MODE_SMALL);
    }

    /**
     * @return Blob
     */
    public static function normal()
    {
        return new self(self::MODE_NORMAL);
    }

    /**
     * @return Blob
     */
    public static function medium()
    {
        return new self(self::MODE_MEDIUM);
    }

    /**
     * @return Blob
     */
    public static function long()
    {
        return new self(self::MODE_LONG);
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }


}