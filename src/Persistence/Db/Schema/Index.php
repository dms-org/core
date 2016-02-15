<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Type\IType;

/**
 * The index class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Index
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isUnique;

    /**
     * @var string[]
     */
    private $columnNames;

    /**
     * Column constructor.
     *
     * @param string   $name
     * @param bool     $isUnique
     * @param string[] $columnNames
     */
    public function __construct(string $name, bool $isUnique, array $columnNames)
    {
        InvalidArgumentException::verify(!empty($columnNames), 'Column names cannot be empty');

        $this->name        = $name;
        $this->isUnique    = $isUnique;
        $this->columnNames = $columnNames;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isUnique() : bool
    {
        return $this->isUnique;
    }

    /**
     * @return string[]
     */
    public function getColumnNames() : array
    {
        return $this->columnNames;
    }

    /**
     * @param string $prefix
     *
     * @return Index
     */
    public function withPrefix(string $prefix) : Index
    {
        return $this
                ->withColumnsPrefixedBy($prefix)
                ->withNamePrefixedBy($prefix);
    }

    /**
     * @param string $prefix
     *
     * @return Index
     */
    public function withColumnsPrefixedBy(string $prefix) : Index
    {
        $prefixedColumns = [];

        foreach ($this->columnNames as $name) {
            $prefixedColumns[] = $prefix . $name;
        }

        return new Index(
                $this->name,
                $this->isUnique,
                $prefixedColumns
        );
    }

    /**
     * @param string $prefix
     *
     * @return Index
     */
    public function withNamePrefixedBy(string $prefix) : Index
    {
        if ($prefix === '') {
            return $this;
        }

        return new Index(
                $prefix . $this->name,
                $this->isUnique,
                $this->columnNames
        );
    }
}