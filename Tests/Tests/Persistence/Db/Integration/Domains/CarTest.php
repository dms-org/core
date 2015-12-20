<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Type\Enum;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Car;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\ConvertibleCar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\FamilyCar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\HatchCar;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\Mapper\CarMapper;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Cars\SedanCar;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;
use Dms\Core\Tests\Persistence\Db\Mock\MockTable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CarTest extends DbIntegrationTest
{
    /**
     * @var MockTable
     */
    protected $carsTable;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return CarMapper::orm();
    }

    /**
     * @inheritDoc
     */
    protected function mapperAndRepoType()
    {
        return Car::class;
    }

    /**
     * @inheritDoc
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);

        $this->carsTable = $db->getTable('cars');
    }

    public function testStructure()
    {
        $this->assertEquals([
                'id'    => new Column('id', Integer::normal()->autoIncrement(), true),
                'brand' => new Column('brand', new Varchar(255)),
                'type'  => new Column('type', new Enum(['sedan', 'hatch', 'convertible', 'family'])),
        ], $this->carsTable->getStructure()->getColumns());
    }

    ////////////////
    // Persisting //
    ////////////////


    public function testPersist()
    {
        $this->repo->saveAll([
                new SedanCar(null, 'Mitsubishi'),
                new FamilyCar(null, 'Toyota'),
                new ConvertibleCar(null, 'Mercedes'),
                new HatchCar(null, 'Lexus'),
        ]);

        $this->assertDatabaseDataSameAs([
                'cars' => [
                        ['id' => 1, 'brand' => 'Mitsubishi', 'type' => 'sedan'],
                        ['id' => 2, 'brand' => 'Toyota', 'type' => 'family'],
                        ['id' => 3, 'brand' => 'Mercedes', 'type' => 'convertible'],
                        ['id' => 4, 'brand' => 'Lexus', 'type' => 'hatch'],
                ]
        ]);
    }


    public function testPersistExisting()
    {
        $this->db->setData([
                'cars' => [
                        ['id' => 1, 'brand' => 'Mitsubishi', 'type' => 'sedan'],
                        ['id' => 2, 'brand' => 'Toyota', 'type' => 'family'],
                        ['id' => 3, 'brand' => 'Mercedes', 'type' => 'convertible'],
                        ['id' => 4, 'brand' => 'Lexus', 'type' => 'hatch'],
                ]
        ]);

        $this->repo->saveAll([
                new HatchCar(4, 'Mitsubishi'),
                new ConvertibleCar(3, 'Lexus'),
                new FamilyCar(2, 'Mercedes'),
                new SedanCar(1, 'Toyota'),
        ]);

        $this->assertDatabaseDataSameAs([
                'cars' => [
                        ['id' => 1, 'brand' => 'Toyota', 'type' => 'sedan'],
                        ['id' => 2, 'brand' => 'Mercedes', 'type' => 'family'],
                        ['id' => 3, 'brand' => 'Lexus', 'type' => 'convertible'],
                        ['id' => 4, 'brand' => 'Mitsubishi', 'type' => 'hatch'],
                ]
        ]);
    }

    /////////////
    // Loading //
    /////////////

    public function testLoadAll()
    {
        $this->db->setData([
                'cars' => [
                        ['id' => 1, 'brand' => 'Mitsubishi', 'type' => 'sedan'],
                        ['id' => 2, 'brand' => 'Toyota', 'type' => 'family'],
                        ['id' => 3, 'brand' => 'Mercedes', 'type' => 'convertible'],
                        ['id' => 4, 'brand' => 'Lexus', 'type' => 'hatch'],
                ]
        ]);

        $this->assertEquals([
                new SedanCar(1, 'Mitsubishi'),
                new FamilyCar(2, 'Toyota'),
                new ConvertibleCar(3, 'Mercedes'),
                new HatchCar(4, 'Lexus'),
        ], $this->repo->getAll());
    }

    //////////////
    // Removing //
    //////////////

    public function testRemove()
    {
        $this->db->setData([
                'cars' => [
                        ['id' => 1, 'brand' => 'Mitsubishi', 'type' => 'sedan'],
                        ['id' => 2, 'brand' => 'Toyota', 'type' => 'family'],
                        ['id' => 3, 'brand' => 'Mercedes', 'type' => 'convertible'],
                        ['id' => 4, 'brand' => 'Lexus', 'type' => 'hatch'],
                ]
        ]);

        $this->repo->removeAllById([1, 3]);

        $this->assertDatabaseDataSameAs([
                'cars' => [
                        ['id' => 2, 'brand' => 'Toyota', 'type' => 'family'],
                        ['id' => 4, 'brand' => 'Lexus', 'type' => 'hatch'],
                ]
        ]);
    }
}