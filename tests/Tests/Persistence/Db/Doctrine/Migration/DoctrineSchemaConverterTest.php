<?php

namespace Dms\Core\Tests\Persistence\Db\Doctrine\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Persistence\Db\Doctrine\Migration\CustomEnumTypeGenerator;
use Dms\Core\Persistence\Db\Doctrine\Migration\DoctrineSchemaConverter;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\DoctrineTypes;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\MediumIntType;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\TinyIntType;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Database;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Dms\Core\Persistence\Db\Schema\Index;
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
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DoctrineSchemaConverterTest extends CmsTestCase
{
    public function expectedConversions()
    {
        DoctrineTypes::load();

        return [
                [new Database([]), new Schema()],
                $this->tablesTest(),
                $this->columnTypesTest(),
                $this->foreignKeysTest(),
                $this->indexesTest(),
        ];
    }

    /**
     * @dataProvider expectedConversions
     */
    public function testConvertsDatabaseCorrectly(Database $originalDb, Schema $expectedDoctrineSchema)
    {
        $converter            = new DoctrineSchemaConverter();
        $actualDoctrineSchema = $converter->convertToDoctrineSchema($originalDb);

        $this->assertEquals($expectedDoctrineSchema, $actualDoctrineSchema);
    }

    private function tablesTest()
    {
        $original = new Database([
                new Table('test', []),
                new Table('another_table', []),
        ]);

        $expected = new Schema();
        $expected->createTable('test');
        $expected->createTable('another_table');

        return [$original, $expected];
    }

    private function columnTypesTest()
    {
        $original = new Database([
                new Table('test', [
                        new Column('primary_key', Integer::normal()->autoIncrement(), true),
                        //
                        new Column('unsigned_int', Integer::normal()->unsigned()),
                        //
                        new Column('int_tiny', Integer::tiny()),
                        new Column('int_small', Integer::small()),
                        new Column('int_normal', Integer::normal()),
                        new Column('int_medium', Integer::medium()),
                        new Column('int_big', Integer::big()),
                        //
                        new Column('varchar', new Varchar(255)),
                        new Column('varchar_nullable', (new Varchar(20))->nullable()),
                        //
                        new Column('text_small', Text::small()),
                        new Column('text_normal', Text::normal()),
                        new Column('text_medium', Text::medium()),
                        new Column('text_long', Text::long()),
                        //
                        new Column('date', new Date()),
                        new Column('time', new Time()),
                        new Column('datetime', new DateTime()),
                        //
                        new Column('enum_abc', new Enum(['a', 'b', 'c'])),
                        new Column('enum_123', new Enum(['1', '2', '3'])),
                        //
                        new Column('decimal_big', new Decimal(30, 5)),
                        new Column('decimal_small', new Decimal(5, 1)),
                        //
                        new Column('bool', new Boolean()),
                        //
                        new Column('blob_small', Blob::small()),
                        new Column('blob_normal', Blob::normal()),
                        new Column('blob_medium', Blob::medium()),
                        new Column('blob_long', Blob::long()),
                ])
        ]);

        $expected  = new Schema();
        $testTable = $expected->createTable('test');

        $testTable->addColumn('primary_key', Type::INTEGER)->setAutoincrement(true);
        $testTable->setPrimaryKey(['primary_key']);

        $testTable->addColumn('unsigned_int', Type::INTEGER)->setUnsigned(true);

        $testTable->addColumn('int_tiny', TinyIntType::TINYINT);
        $testTable->addColumn('int_small', Type::SMALLINT);
        $testTable->addColumn('int_normal', Type::INTEGER);
        $testTable->addColumn('int_medium', MediumIntType::MEDIUMINT);
        $testTable->addColumn('int_big', Type::BIGINT);

        $testTable->addColumn('varchar', Type::STRING)->setLength(255);
        $testTable->addColumn('varchar_nullable', Type::STRING)->setLength(20)->setNotnull(false);

        $testTable->addColumn('text_small', Type::TEXT)->setLength(pow(2, 8) - 1);
        $testTable->addColumn('text_normal', Type::TEXT)->setLength(pow(2, 16) - 1);
        $testTable->addColumn('text_medium', Type::TEXT)->setLength(pow(2, 24) - 1);
        $testTable->addColumn('text_long', Type::TEXT)->setLength(pow(2, 32) - 1);

        $testTable->addColumn('date', Type::DATE);
        $testTable->addColumn('time', Type::TIME);
        $testTable->addColumn('datetime', Type::DATETIME);

        $testTable->addColumn('enum_abc', CustomEnumTypeGenerator::generate(['a', 'b', 'c']));
        $testTable->addColumn('enum_123', CustomEnumTypeGenerator::generate(['1', '2', '3']));

        $testTable->addColumn('decimal_big', Type::DECIMAL)->setPrecision(30)->setScale(5);
        $testTable->addColumn('decimal_small', Type::DECIMAL)->setPrecision(5)->setScale(1);

        $testTable->addColumn('bool', Type::BOOLEAN);

        $testTable->addColumn('blob_small', Type::BLOB)->setLength(pow(2, 8) - 1);
        $testTable->addColumn('blob_normal', Type::BLOB)->setLength(pow(2, 16) - 1);
        $testTable->addColumn('blob_medium', Type::BLOB)->setLength(pow(2, 24) - 1);
        $testTable->addColumn('blob_long', Type::BLOB)->setLength(pow(2, 32) - 1);

        return [$original, $expected];
    }

    public function foreignKeysTest()
    {
        $original = new Database([
                new Table('test', [new Column('id', Integer::normal()), new Column('id2', Integer::normal())]),
                new Table('another_table', [new Column('fk', Integer::normal())], [], [
                        new ForeignKey(
                                'fk_name1',
                                ['fk'],
                                'test',
                                ['id'],
                                ForeignKeyMode::CASCADE,
                                ForeignKeyMode::CASCADE
                        ),
                        new ForeignKey(
                                'fk_name2',
                                ['fk'],
                                'test',
                                ['id2'],
                                ForeignKeyMode::SET_NULL,
                                ForeignKeyMode::DO_NOTHING
                        ),
                ])
        ]);

        $expected  = new Schema();
        $testTable = $expected->createTable('test');
        $testTable->addColumn('id', Type::INTEGER);
        $testTable->addColumn('id2', Type::INTEGER);

        $anotherTable = $expected->createTable('another_table');
        $anotherTable->addColumn('fk', Type::INTEGER);
        $anotherTable->addForeignKeyConstraint(
                'test',
                ['fk'],
                ['id'],
                ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
                'fk_name1'
        );
        $anotherTable->addForeignKeyConstraint(
                'test',
                ['fk'],
                ['id2'],
                ['onDelete' => 'SET NULL', 'onUpdate' => 'NO ACTION'],
                'fk_name2'
        );

        return [$original, $expected];
    }

    public function indexesTest()
    {

        $original = new Database([
                new Table('test', [new Column('data1', Integer::normal()), new Column('data2', Integer::normal())], [
                        new Index('index_name1', false, ['data1']),
                        new Index('index_name2', true, ['data1', 'data2']),
                ]),
        ]);

        $expected  = new Schema();
        $testTable = $expected->createTable('test');
        $testTable->addColumn('data1', Type::INTEGER);
        $testTable->addColumn('data2', Type::INTEGER);

        $testTable->addIndex(['data1'], 'index_name1');
        $testTable->addUniqueIndex(['data1', 'data2'], 'index_name2');

        return [$original, $expected];
    }
}