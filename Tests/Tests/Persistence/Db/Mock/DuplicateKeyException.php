<?php

namespace Dms\Core\Tests\Persistence\Db\Mock;

use Dms\Core\Exception\BaseException;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DuplicateKeyException extends BaseException
{
    public function __construct(Table $table, Column $column)
    {
        parent::__construct(
                "Duplicate primary key inserted for: {$table->getName()}.{$column->getName()}"
        );
    }

}