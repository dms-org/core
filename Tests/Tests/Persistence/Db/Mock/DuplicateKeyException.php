<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mock;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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