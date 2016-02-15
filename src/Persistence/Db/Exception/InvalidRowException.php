<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Exception;

use Dms\Core\Exception\BaseException;
use Dms\Core\Persistence\Db\Row;

/**
 * The invalid row exception class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidRowException extends BaseException
{
    /**
     * @var Row
     */
    protected $row;

    public function __construct(Row $row, $message)
    {
        parent::__construct($message);
        $this->row = $row;
    }

    /**
     * @return Row
     */
    final public function getRow() : \Dms\Core\Persistence\Db\Row
    {
        return $this->row;
    }
}