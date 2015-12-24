<?php

namespace Dms\Core\Persistence\Db\Schema\Type;

/**
 * The db integer type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Integer extends Type
{
    const MODE_TINY = 'tiny';
    const MODE_SMALL = 'small';
    const MODE_MEDIUM = 'medium';
    const MODE_NORMAL = 'normal';
    const MODE_BIG = 'big';

    /**
     * @var string
     */
    private $mode;

    /**
     * @var bool
     */
    private $autoIncrement = false;

    /**
     * Int constructor.
     *
     * @param string $mode
     */
    private function __construct($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return self
     */
    public static function tiny()
    {
        return new self(self::MODE_TINY);
    }

    /**
     * @return self
     */
    public static function small()
    {
        return new self(self::MODE_SMALL);
    }

    /**
     * @return self
     */
    public static function medium()
    {
        return new self(self::MODE_MEDIUM);
    }

    /**
     * @return self
     */
    public static function normal()
    {
        return new self(self::MODE_NORMAL);
    }

    /**
     * @return self
     */
    public static function big()
    {
        return new self(self::MODE_BIG);
    }

    /**
     * @return self
     */
    public function autoIncrement()
    {
        $clone = clone $this;
        $clone->autoIncrement = true;

        return $clone;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}