<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mock;

use Iddigital\Cms\Core\Exception\BaseException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyConstraintException extends BaseException
{
    public function __construct(MockForeignKey $foreignKey, array $invalidKeys)
    {
        $mainTable        = $foreignKey->getMainTable()->getName();
        $mainColumn       = $foreignKey->getMainColumn()->getName();
        $referencedTable  = $foreignKey->getReferencedTable()->getName();
        $referencedColumn = $foreignKey->getReferencedColumn()->getName();
        $keys             = implode(', ', $invalidKeys);
        parent::__construct(
                "The foreign key constraint failed on {$mainTable}.{$mainColumn} = {$referencedTable}.{$referencedColumn}: invalid {$mainTable}.{$mainColumn} values, {$keys}"
        );
    }

}