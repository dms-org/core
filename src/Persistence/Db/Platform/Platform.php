<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Platform;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\ResequenceOrderIndexColumn;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Query\Update;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type\Blob;
use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Date;
use Dms\Core\Persistence\Db\Schema\Type\DateTime;
use Dms\Core\Persistence\Db\Schema\Type\Decimal;
use Dms\Core\Persistence\Db\Schema\Type\Enum;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Text;
use Dms\Core\Persistence\Db\Schema\Type\Time;
use Dms\Core\Persistence\Db\Schema\Type\Type;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

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
        $this->timezone       = new \DateTimeZone('UTC');
    }

    /**
     * @return array
     */
    protected function typeMap() : array
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
    abstract protected function dateFormatString() : string;

    /**
     * @return string
     */
    abstract protected function dateTimeFormatString() : string;

    /**
     * @return string
     */
    abstract protected function timeFormatString() : string;

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
    final public function mapResultSetToDbFormat(RowSet $rows, string $lockingColumnDataPrefix = null) : array
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

            foreach ($row->getLockingColumnData() as $column => $value) {
                if (!$lockingColumnDataPrefix) {
                    throw InvalidArgumentException::format(
                            'Invalid call to %s: missing $lockingColumnDataPrefix argument and locking data found in column %s',
                            __METHOD__, $column
                    );
                }

                $prefixedName = $lockingColumnDataPrefix . $column;
                if (isset($columnDateFormatMap[$column])) {
                    $rowData[$prefixedName] = $value instanceof \DateTimeInterface
                            ? $value->format($columnDateFormatMap[$column])
                            : null;
                } else {
                    $rowData[$prefixedName] = $value;
                }
            }

            $results[$key] = $rowData;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    final public function mapResultSetToPhpForm(Table $table, array $results) : RowSet
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
            $type  = $column->getType();
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
    public function compileSelect(Select $query) : CompiledQuery
    {
        if (empty($query->getAliasColumnMap())) {
            throw InvalidArgumentException::format('Cannot compile select: no columns have been specified');
        }

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
    public function compileUpdate(Update $query) : CompiledQuery
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
    public function compileDelete(Delete $query) : CompiledQuery
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

    /**
     *{@inheritDoc}
     */
    public function compileResequenceOrderIndexColumn(ResequenceOrderIndexColumn $query) : CompiledQuery
    {
        $compiled = new CompiledQueryBuilder();
        $this->compileResequenceOrderIndexColumnQuery($query, $compiled);

        return $compiled->build();
    }

    /**
     * @param ResequenceOrderIndexColumn $query
     * @param CompiledQueryBuilder       $compiled
     *
     * @return void
     */
    abstract protected function compileResequenceOrderIndexColumnQuery(ResequenceOrderIndexColumn $query, CompiledQueryBuilder $compiled);
}