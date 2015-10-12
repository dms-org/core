<?php

namespace Iddigital\Cms\Core\Table\Data;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Table\IColumn;
use Iddigital\Cms\Core\Table\IColumnComponent;
use Iddigital\Cms\Core\Table\ITableRow;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The table row class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRow implements ITableRow
{
    /**
     * @var array[]
     */
    protected $data;

    /**
     * TableRow constructor.
     *
     * @param array[] $data
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $data)
    {
        foreach ($data as $columnData) {
            if (!is_array($columnData)) {
                throw InvalidArgumentException::format('Data must only contain arrays, %s found', Debug::getType($columnData));
            }
        }

        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getCellData($column)
    {
        $columnName = $column instanceof IColumn ? $column->getName() : $column;

        if (!array_key_exists($columnName, $this->data)) {
            throw InvalidArgumentException::format(
                    'Invalid column supplied to row: expecting one of (%s), \'%s\' given',
                    Debug::formatValues(array_keys($this->data)), $columnName
            );
        }

        return $this->data[$columnName];
    }

    /**
     * @inheritDoc
     */
    public function getCellComponentData($column, $component = null)
    {
        $cellData = $this->getCellData($column);

        $componentName = $component instanceof IColumnComponent ? $component->getName() : $component;

        if ($componentName === null) {
            if (count($cellData) === 1) {
                return reset($cellData);
            } else {
                throw InvalidArgumentException::format(
                        'Invalid column component supplied to row: expecting one of (%s), null given',
                        Debug::formatValues(array_keys($cellData))
                );
            }
        }

        if (!isset($cellData[$componentName]) && !array_key_exists($componentName, $cellData)) {
            throw InvalidArgumentException::format(
                    'Invalid column component supplied to row: expecting one of (%s), %s given',
                    Debug::formatValues(array_keys($cellData)), $componentName
            );
        }

        return $cellData[$componentName];
    }

    /**
     * @param string $componentId
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function offsetExists($componentId)
    {
        list($columnName, $componentName) = explode('.', $componentId) + [1 => null];

        if (!array_key_exists($columnName, $this->data)) {
            return false;
        }

        $cellData = $this->getCellData($columnName);

        if (!$componentName) {
            return count($cellData) === 1;
        } else {
            return array_key_exists($componentName, $cellData);
        }
    }

    /**
     * @param string $componentId
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function offsetGet($componentId)
    {
        list($columnName, $componentName) = explode('.', $componentId) + [1 => null];

        return $this->getCellComponentData($columnName, $componentName);
    }

    public function offsetSet($offset, $value)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function offsetUnset($offset)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}