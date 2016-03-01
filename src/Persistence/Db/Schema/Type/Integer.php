<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

use Dms\Core\Model\Type\Builder\Type as PhpType;
use Dms\Core\Model\Type\IType;

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
    private $unsigned = false;

    /**
     * @var bool
     */
    private $autoIncrement = false;

    /**
     * Int constructor.
     *
     * @param string $mode
     */
    private function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @inheritDoc
     */
    protected function loadPhpType() : IType
    {
        return PhpType::int();
    }

    /**
     * @return self
     */
    public static function tiny() : self
    {
        return new self(self::MODE_TINY);
    }

    /**
     * @return self
     */
    public static function small() : self
    {
        return new self(self::MODE_SMALL);
    }

    /**
     * @return self
     */
    public static function medium() : self
    {
        return new self(self::MODE_MEDIUM);
    }

    /**
     * @return self
     */
    public static function normal() : self
    {
        return new self(self::MODE_NORMAL);
    }

    /**
     * @return self
     */
    public static function big() : self
    {
        return new self(self::MODE_BIG);
    }

    /**
     * @return self
     */
    public function autoIncrement() : self
    {
        $clone = clone $this;
        $clone->autoIncrement = true;

        return $clone;
    }

    /**
     * @return self
     */
    public function unsigned() : self
    {
        $clone = clone $this;
        $clone->unsigned = true;

        return $clone;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement() : bool
    {
        return $this->autoIncrement;
    }

    /**
     * @return boolean
     */
    public function isUnsigned() : bool
    {
        return $this->unsigned;
    }

    /**
     * @return string
     */
    public function getMode() : string
    {
        return $this->mode;
    }
}