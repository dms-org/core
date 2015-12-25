<?php

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
    public function __construct($name, $isUnique, array $columnNames)
    {
        InvalidArgumentException::verify(!empty($columnNames), 'Column names cannot be empty');

        $this->name        = $name;
        $this->isUnique    = $isUnique;
        $this->columnNames = $columnNames;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isUnique()
    {
        return $this->isUnique;
    }

    /**
     * @return string[]
     */
    public function getColumnNames()
    {
        return $this->columnNames;
    }

    /**
     * @param string $prefix
     *
     * @return Index
     */
    public function withPrefix($prefix)
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
    public function withColumnsPrefixedBy($prefix)
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
    public function withNamePrefixedBy($prefix)
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