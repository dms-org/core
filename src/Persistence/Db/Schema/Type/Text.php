<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

use Dms\Core\Model\Type\Builder\Type as PhpType;
use Dms\Core\Model\Type\IType;

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
    private function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @inheritDoc
     */
    protected function loadPhpType() : IType
    {
        return PhpType::string();
    }

    /**
     * @return Text
     */
    public static function small() : Text
    {
        return new self(self::MODE_SMALL);
    }

    /**
     * @return Text
     */
    public static function normal() : Text
    {
        return new self(self::MODE_NORMAL);
    }

    /**
     * @return Text
     */
    public static function medium() : Text
    {
        return new self(self::MODE_MEDIUM);
    }

    /**
     * @return Text
     */
    public static function long() : Text
    {
        return new self(self::MODE_LONG);
    }

    /**
     * @return string
     */
    public function getMode() : string
    {
        return $this->mode;
    }


}