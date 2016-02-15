<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine\Migration;

use Dms\Core\Persistence\Db\Doctrine\DoctrineConnection;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\DoctrineTypes;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\SchemaDiff;

/**
 * The migration generator base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MigrationGenerator
{
    /**
     * @var DoctrineSchemaConverter
     */
    protected $databaseConverter;

    /**
     * MigrationGenerator constructor.
     */
    public function __construct()
    {
        DoctrineTypes::load();
        $this->databaseConverter = new DoctrineSchemaConverter();
    }

    /**
     * @param DoctrineConnection $connection
     * @param IOrm               $orm
     * @param string             $migrationName
     *
     * @return string|null the migration file path or null if no migration is required
     */
    public function generateMigration(DoctrineConnection $connection, IOrm $orm, string $migrationName)
    {
        $doctrine = $connection->getDoctrineConnection();

        $currentSchema  = $doctrine->getSchemaManager()->createSchema();
        $expectedSchema = $this->databaseConverter->convertToDoctrineSchema($orm->getDatabase());

        $diff        = Comparator::compareSchemas($currentSchema, $expectedSchema);
        $reverseDiff = Comparator::compareSchemas($expectedSchema, $currentSchema);

        if ($this->isSchemaDiffEmpty($diff) && $this->isSchemaDiffEmpty($reverseDiff)) {
            return null;
        }

        return $this->createMigration($diff, $reverseDiff, $migrationName);
    }

    /**
     * @param SchemaDiff $diff
     * @param SchemaDiff $reverseDiff
     * @param string     $migrationName
     *
     * @return string|null
     */
    abstract protected function createMigration(SchemaDiff $diff, SchemaDiff $reverseDiff, string $migrationName);

    /**
     * @param SchemaDiff $diff
     *
     * @return bool
     */
    protected function isSchemaDiffEmpty(SchemaDiff $diff) : bool
    {
        return empty(array_filter([
                $diff->changedSequences,
                $diff->changedTables,
                $diff->newNamespaces,
                $diff->newSequences,
                $diff->newTables,
                $diff->removedTables,
                $diff->removedNamespaces,
                $diff->removedSequences,
                $diff->orphanedForeignKeys,
        ]));
    }
}