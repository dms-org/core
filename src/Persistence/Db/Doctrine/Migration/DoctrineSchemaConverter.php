<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine\Migration;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\MediumIntType;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\TinyIntType;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Database;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Index;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Schema\Type;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Table as DoctrineTable;
use Doctrine\DBAL\Types\Type as DoctrineType;

/**
 * The doctrine schema converter maps the built in
 * database classes to doctrine's equivalent schema classes.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineSchemaConverter
{
    /**
     * Converts the database to the equivalent doctrine schema.
     *
     * @param Database $database
     * @param AbstractPlatform $platform
     *
     * @return Schema
     */
    public function convertToDoctrineSchema(Database $database, AbstractPlatform $platform) : \Doctrine\DBAL\Schema\Schema
    {
        $schema = new Schema();

        foreach ($database->getTables() as $table) {
            $this->mapTable($schema, $table, $platform);
        }

        return $schema;
    }

    private function mapTable(Schema $schema, Table $table, AbstractPlatform $platform)
    {
        $doctrineTable = $schema->createTable($table->getName());

        foreach ($table->getColumns() as $column) {
            $this->mapColumn($doctrineTable, $column, $platform);
        }

        if ($table->hasPrimaryKeyColumn()) {
            $doctrineTable->setPrimaryKey([$table->getPrimaryKeyColumnName()]);
        }

        foreach ($table->getIndexes() as $index) {
            $this->mapIndex($doctrineTable, $index);
        }

        foreach ($table->getForeignKeys() as $foreignKey) {
            $this->mapForeignKey($doctrineTable, $foreignKey);
        }
    }

    private function mapIndex(DoctrineTable $doctrineTable, Index $index)
    {
        if ($index->isUnique()) {
            $doctrineTable->addUniqueIndex($index->getColumnNames(), $index->getName());
        } else {
            $doctrineTable->addIndex($index->getColumnNames(), $index->getName());
        }
    }

    private function mapForeignKey(DoctrineTable $doctrineTable, ForeignKey $foreignKey)
    {
        $doctrineTable->addForeignKeyConstraint(
                $foreignKey->getReferencedTableName(),
                $foreignKey->getLocalColumnNames(),
                $foreignKey->getReferencedColumnNames(),
                [
                        'onUpdate' => $this->mapForeignKeyMode($foreignKey->getOnUpdateMode()),
                        'onDelete' => $this->mapForeignKeyMode($foreignKey->getOnDeleteMode()),
                ],
                $foreignKey->getName()
        );
    }

    private function mapForeignKeyMode($mode)
    {
        switch ($mode) {
            case ForeignKeyMode::DO_NOTHING:
                return 'NO ACTION';

            case ForeignKeyMode::CASCADE:
                return 'CASCADE';

            case ForeignKeyMode::SET_NULL:
                return 'SET NULL';
        }

        ForeignKeyMode::validate($mode);
    }

    private function mapColumn(DoctrineTable $doctrineTable, Column $column, AbstractPlatform $platform)
    {
        list($typeName, $options) = $this->mapColumnType($column->getType(), $platform);

        $doctrineTable->addColumn(
                $column->getName(),
                $typeName,
                $options
        );
    }

    private function mapColumnType(Type\Type $type, AbstractPlatform $platform)
    {
        $options = [];

        if ($type->isNullable()) {
            $options['notnull'] = false;
        }

        switch (true) {
            case $type instanceof Type\Blob:
                $options['length'] = $this->getBlobModeLength($type->getMode());

                return [DoctrineType::BLOB, $options];

            case $type instanceof Type\Boolean:
                return [DoctrineType::BOOLEAN, $options];

            case $type instanceof Type\Date:
                return [DoctrineType::DATE, $options];

            case $type instanceof Type\DateTime:
                return [DoctrineType::DATETIME, $options];

            case $type instanceof Type\Decimal:
                $options['scale']     = $type->getDecimalPoints();
                $options['precision'] = $type->getPrecision();

                return [DoctrineType::DECIMAL, $options];

            case $type instanceof Type\Enum && $platform instanceof MySqlPlatform:
                return [CustomEnumTypeGenerator::generate($type->getOptions()), $options];

            case $type instanceof Type\Enum:
                $options['length'] = 255;

                return [DoctrineType::STRING, $options];

            case $type instanceof Type\Integer:
                $options['autoincrement'] = $type->isAutoIncrement();
                $options['unsigned']      = $type->isUnsigned();

                return [$this->getIntegerType($type->getMode()), $options];

            case $type instanceof Type\Text:
                $options['length'] = $this->getTextModeLength($type->getMode());

                return [DoctrineType::TEXT, $options];

            case $type instanceof Type\Time:
                return [DoctrineType::TIME, $options];

            case $type instanceof Type\Varchar:
                $options['length'] = $type->getLength();

                return [DoctrineType::STRING, $options];
        }

        throw InvalidArgumentException::format('Unknown column type: %s', get_class($type));
    }

    private function getIntegerType($mode)
    {
        switch ($mode) {
            case Type\Integer::MODE_BIG:
                return DoctrineType::BIGINT;

            case Type\Integer::MODE_NORMAL:
                return DoctrineType::INTEGER;

            case Type\Integer::MODE_MEDIUM:
                return MediumIntType::MEDIUMINT;

            case Type\Integer::MODE_SMALL:
                return DoctrineType::SMALLINT;

            case Type\Integer::MODE_TINY:
                return TinyIntType::TINYINT;
        }

        throw InvalidArgumentException::format('Unknown integer type: %s', $mode);
    }

    private function getTextModeLength($mode)
    {
        switch ($mode) {
            case Type\Text::MODE_LONG:
                return pow(2, 32) - 1;

            case Type\Text::MODE_MEDIUM:
                return pow(2, 24) - 1;

            case Type\Text::MODE_NORMAL:
                return pow(2, 16) - 1;

            case Type\Text::MODE_SMALL:
                return pow(2, 8) - 1;
        }

        throw InvalidArgumentException::format('Unknown text type: %s', $mode);
    }

    private function getBlobModeLength($mode)
    {
        switch ($mode) {
            case Type\Blob::MODE_LONG:
                return pow(2, 32) - 1;

            case Type\Blob::MODE_MEDIUM:
                return pow(2, 24) - 1;

            case Type\Blob::MODE_NORMAL:
                return pow(2, 16) - 1;

            case Type\Blob::MODE_SMALL:
                return pow(2, 8) - 1;
        }

        throw InvalidArgumentException::format('Unknown text type: %s', $mode);
    }
}