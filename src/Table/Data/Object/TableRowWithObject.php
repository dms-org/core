<?php declare(strict_types = 1);

namespace Dms\Core\Table\Data\Object;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Table\Data\TableRow;
use Dms\Core\Table\IColumn;
use Dms\Core\Table\IColumnComponent;
use Dms\Core\Table\ITableRow;
use Dms\Core\Util\Debug;

/**
 * The table row class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRowWithObject extends TableRow
{
    /**
     * @var ITypedObject
     */
    protected $object;

    /**
     * TableRowWithObject constructor.
     *
     * @param array        $data
     * @param ITypedObject $object
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $data, ITypedObject $object)
    {
        parent::__construct($data);
        $this->object = $object;
    }

    /**
     * @inheritDoc
     */
    public function getObject() : ITypedObject
    {
        return $this->object;
    }
}