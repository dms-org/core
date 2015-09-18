<?php

namespace Iddigital\Cms\Core\Persistence\Db\Schema\Type;

/**
 * The db text type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Text extends Type
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
     * Text constructor.
     *
     * @param string $mode
     */
    private function __construct($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return Text
     */
    public static function small()
    {
        return new self(self::MODE_SMALL);
    }

    /**
     * @return Text
     */
    public static function normal()
    {
        return new self(self::MODE_NORMAL);
    }

    /**
     * @return Text
     */
    public static function medium()
    {
        return new self(self::MODE_MEDIUM);
    }

    /**
     * @return Text
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