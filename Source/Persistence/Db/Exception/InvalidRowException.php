<?php

namespace Iddigital\Cms\Core\Persistence\Db\Exception;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Persistence\Db\Row;

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
    final public function getRow()
    {
        return $this->row;
    }
}