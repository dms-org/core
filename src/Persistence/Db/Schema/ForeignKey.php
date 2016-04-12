<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\Type\IType;

/**
 * The foreign key class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKey
{
    const CONVENTION_PREFIX = 'fk_';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $localColumnNames;

    /**
     * @var string
     */
    private $referencedTableName;

    /**
     * @var string[]
     */
    private $referencedColumnNames;

    /**
     * @see ForeignKeyMode
     * @var string
     */
    private $onDeleteMode;

    /**
     * @see ForeignKeyMode
     * @var string
     */
    private $onUpdateMode;

    /**
     * ForeignKey constructor.
     *
     * @param string   $name
     * @param string[] $localColumnNames
     * @param string   $referencedTableName
     * @param string[] $referencedColumnNames
     * @param string   $onDeleteMode
     * @param string   $onUpdateMode
     */
    public function __construct(
        string $name,
        array $localColumnNames,
        string $referencedTableName,
        array $referencedColumnNames,
        string $onDeleteMode,
        string $onUpdateMode
    ) {
        InvalidArgumentException::verifyAll(__METHOD__, 'localColumnNames', $localColumnNames, 'is_string');
        InvalidArgumentException::verifyAll(__METHOD__, 'referencedColumnNames', $referencedColumnNames, 'is_string');

        InvalidArgumentException::verify(
            count($localColumnNames) === count($referencedColumnNames),
            'Local columns must match the amount of referenced columns: %s != %s',
            count($localColumnNames), count($referencedColumnNames)
        );


        InvalidArgumentException::verify(!empty($localColumnNames), 'Column names cannot be empty');

        ForeignKeyMode::validate($onDeleteMode);
        ForeignKeyMode::validate($onUpdateMode);

        $this->name                  = $name;
        $this->localColumnNames      = array_values($localColumnNames);
        $this->referencedTableName   = $referencedTableName;
        $this->referencedColumnNames = array_values($referencedColumnNames);
        $this->onDeleteMode          = $onDeleteMode;
        $this->onUpdateMode          = $onUpdateMode;
    }

    /**
     * Creates a new foreign key with the name using the convention:
     * fk_{table}_{local_columns}_{referenced_table}
     *
     * @param string   $tableName
     * @param string[] $localColumnNames
     * @param string   $referencedTableName
     * @param string[] $referencedColumnNames
     * @param string   $onDeleteMode
     * @param string   $onUpdateMode
     *
     * @return ForeignKey
     */
    public static function createWithNamingConvention(
        string $tableName,
        array $localColumnNames,
        string $referencedTableName,
        array $referencedColumnNames,
        string $onDeleteMode,
        string $onUpdateMode
    ) : ForeignKey
    {
        $fkName = self::CONVENTION_PREFIX . implode('_', [
                $tableName,
                implode('_', $localColumnNames),
                $referencedTableName,
            ]);

        return new self(
            $fkName,
            $localColumnNames,
            $referencedTableName,
            $referencedColumnNames,
            $onDeleteMode,
            $onUpdateMode
        );
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getLocalColumnNames() : array
    {
        return $this->localColumnNames;
    }

    /**
     * @return string
     */
    public function getReferencedTableName() : string
    {
        return $this->referencedTableName;
    }

    /**
     * @return string[]
     */
    public function getReferencedColumnNames() : array
    {
        return $this->referencedColumnNames;
    }

    /**
     * @return string
     */
    public function getOnDeleteMode() : string
    {
        return $this->onDeleteMode;
    }

    /**
     * @return string
     */
    public function getOnUpdateMode() : string
    {
        return $this->onUpdateMode;
    }

    /**
     * @return bool
     */
    public function requiresNullableColumns() : bool
    {
        return $this->onUpdateMode === ForeignKeyMode::SET_NULL
        || $this->onDeleteMode === ForeignKeyMode::SET_NULL;
    }

    /**
     * Gets the foreign key with the *local* columns prefixed
     * and the name prefixed.
     *
     * @param string $prefix
     *
     * @return ForeignKey
     */
    public function withPrefix(string $prefix) : ForeignKey
    {
        return $this
            ->withLocalColumnsPrefixedBy($prefix)
            ->withNamePrefixedBy($prefix);
    }

    /**
     * @param string $prefix
     *
     * @return ForeignKey
     */
    public function withLocalColumnsPrefixedBy(string $prefix) : ForeignKey
    {
        $prefixedLocalColumns = [];

        foreach ($this->localColumnNames as $column) {
            $prefixedLocalColumns[] = $prefix . $column;
        }

        return new ForeignKey(
            $this->name,
            $prefixedLocalColumns,
            $this->referencedTableName,
            $this->referencedColumnNames,
            $this->onDeleteMode,
            $this->onUpdateMode
        );
    }

    /**
     * @param string $prefix
     *
     * @return ForeignKey
     */
    public function withNamePrefixedBy(string $prefix) : ForeignKey
    {
        if ($prefix === '') {
            return $this;
        }

        if (strpos($this->name, self::CONVENTION_PREFIX) === 0) {
            $name = self::CONVENTION_PREFIX . $prefix . substr($this->name, strlen(self::CONVENTION_PREFIX));
        } else {
            $name = $prefix . $this->name;
        }

        return new ForeignKey(
            $name,
            $this->localColumnNames,
            $this->referencedTableName,
            $this->referencedColumnNames,
            $this->onDeleteMode,
            $this->onUpdateMode
        );
    }
}