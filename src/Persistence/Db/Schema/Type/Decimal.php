<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

/**
 * The db decimal type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Decimal extends Type
{
    /**
     * @var int
     */
    private $precision;

    /**
     * @var int
     */
    private $decimalPoints;

    /**
     * Decimal constructor.
     *
     * @param int $precision
     * @param int $decimalPoints
     */
    public function __construct(int $precision, int $decimalPoints)
    {
        $this->precision     = $precision;
        $this->decimalPoints = $decimalPoints;
    }

    /**
     * @return int
     */
    public function getPrecision() : int
    {
        return $this->precision;
    }

    /**
     * @return int
     */
    public function getDecimalPoints() : int
    {
        return $this->decimalPoints;
    }
}