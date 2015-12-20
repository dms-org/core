<?php

namespace Dms\Core\Persistence\Db\Doctrine\Migration;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\SchemaDiff;
use Dms\Core\Persistence\Db\Doctrine\DoctrineConnection;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\DoctrineTypes;
use Dms\Core\Persistence\Db\Mapping\IOrm;

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
     *
     * @return void
     */
    public function generateMigration(DoctrineConnection $connection, IOrm $orm)
    {
        $doctrine = $connection->getDoctrineConnection();

        $currentSchema  = $doctrine->getSchemaManager()->createSchema();
        $expectedSchema = $this->databaseConverter->convertToDoctrineSchema($orm->getDatabase());

        $diff = Comparator::compareSchemas($currentSchema, $expectedSchema);

        $this->createMigration($diff);
    }

    /**
     * @param SchemaDiff $diff
     *
     * @return void
     */
    abstract protected function createMigration(SchemaDiff $diff);
}