<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

use Dms\Core\Model\Type\Builder\Type as PhpType;
use Dms\Core\Model\Type\IType;

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
     * @inheritDoc
     */
    protected function loadPhpType() : IType
    {
        return PhpType::string();
    }

    /**
     * @return int
     */
    public function getLength() : int
    {
        return $this->length;
    }
}