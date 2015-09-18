<?php

namespace Iddigital\Cms\Core\Persistence\Db\Platform;

use Iddigital\Cms\Core\Persistence\Db\Query\Delete;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\Db\Query\Update;
use Iddigital\Cms\Core\Persistence\Db\Query\Upsert;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\RowSet;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Blob;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Date;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\DateTime;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Decimal;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Enum;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Text;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Time;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * The db platform base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Platform implements IPlatform
{
    /**
     * @var array
     */
    private $typeFormatMap;

    /**
     * @var array
     */
    private $typePhpTypeMap;

    /**
     * @var \DateTimeZone
     */
    private $timezone;

    /**
     * Platform constructor.
     */
    public function __construct()
    {
        $this->typeFormatMap = [
                Date::class     => $this->dateFormatString(),
                DateTime::class => $this->dateTimeFormatString(),
                Time::class     => $this->timeFormatString(),
        ];

        $this->typePhpTypeMap = $this->typeMap();
        $this->timezone = new \DateTimeZone('UTC');
    }

    /**
     * @return array
     */
    protected function typeMap()
    {
        return [
                Varchar::class => 'string',
                Blob::class    => 'string',
                Text::class    => 'string',
                Decimal::class => 'double',
                Enum::class    => 'string',
                Integer::class => 'integer',
                Boolean::class => 'boolean',
        ];
    }

    /**
     * @return string
     */
    abstract protected function dateFormatString();

    /**
     * @return string
     */
    abstract protected function dateTimeFormatString();

    /**
     * @return string
     */
    abstract protected function timeFormatString();

    /**
     * {@inheritDoc}
     */
    final public function mapValueToDbFormat(Type $type, $value)
    {
        $typeClass = get_class($type);
        if (isset($this->typeFormatMap[$typeClass])) {
            return $value instanceof \DateTimeInterface
                    ? $value->format($this->typeFormatMap[$typeClass])
                    : null;
        } elseif (isset($this->typePhpTypeMap[$typeClass]) && !($type->isNullable() && $value === null)) {
            settype($value, $this->typePhpTypeMap[$typeClass]);
        }

        return $value;
    }


    /**
     * {@inheritDoc}
     */
    final public function mapResultSetToDbFormat(RowSet $rows)
    {
        $results             = [];
        $columnDateFormatMap = $this->getColumnDateFormatMap($rows->getTable());

        foreach ($rows->getRows() as $key => $row) {
            $rowData = $row->getColumnData();

            foreach ($columnDateFormatMap as $column => $dateFormat) {
                $rowData[$column] = $rowData[$column] instanceof \DateTimeInterface
                        ? $rowData[$column]->format($dateFormat)
                        : null;
            }

            $results[$key] = $rowData;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    final public function mapResultSetToPhpForm(Table $table, array $results)
    {
        $rows                = [];
        $columnDateFormatMap = $this->getColumnDateFormatMap($table);
        $columnTypeMap       = $this->getColumnMap($table, $this->typePhpTypeMap);

        foreach ($results as $rowData) {
            foreach ($columnDateFormatMap as $column => $dateFormat) {
                $rowData[$column] = $rowData[$column]
                        ? \DateTimeImmutable::createFromFormat('!' . $dateFormat, $rowData[$column], $this->timezone)
                        : null;
            }

            foreach ($columnTypeMap as $column => $type) {
                if ($rowData[$column] !== null) {
                    settype($rowData[$column], $type);
                }
            }

            $rows[] = new Row($table, $rowData);
        }

        return new RowSet($table, $rows);
    }

    protected function getColumnDateFormatMap(Table $table)
    {
        return $this->getColumnMap($table, $this->typeFormatMap);
    }

    protected function getColumnMap(Table $table, array $typeMap)
    {
        $columnMap = [];

        foreach ($table->getColumns() as $column) {
            $type = $column->getType();
            $class = get_class($type);

            if (isset($typeMap[$class])) {
                $columnMap[$column->getName()] = $typeMap[$class];
            }
        }

        return $columnMap;
    }

    /**
     *{@inheritDoc}
     */
    public function compileSelect(Select $query)
    {
        $compiled = new CompiledQueryBuilder();
        $this->compileSelectQuery($query, $compiled);

        return $compiled->build();
    }

    /**
     * @param Select               $query
     * @param CompiledQueryBuilder $compiled
     *
     * @return void
     */
    abstract protected function compileSelectQuery(Select $query, CompiledQueryBuilder $compiled);

    /**
     *{@inheritDoc}
     */
    public function compileUpdate(Update $query)
    {
        $compiled = new CompiledQueryBuilder();
        $this->compileUpdateQuery($query, $compiled);

        return $compiled->build();
    }

    /**
     * @param Update               $query
     * @param CompiledQueryBuilder $compiled
     *
     * @return void
     */
    abstract protected function compileUpdateQuery(Update $query, CompiledQueryBuilder $compiled);

    /**
     *{@inheritDoc}
     */
    public function compileDelete(Delete $query)
    {
        $compiled = new CompiledQueryBuilder();
        $this->compileDeleteQuery($query, $compiled);

        return $compiled->build();
    }

    /**
     * @param Delete               $query
     * @param CompiledQueryBuilder $compiled
     *
     * @return void
     */
    abstract protected function compileDeleteQuery(Delete $query, CompiledQueryBuilder $compiled);
}