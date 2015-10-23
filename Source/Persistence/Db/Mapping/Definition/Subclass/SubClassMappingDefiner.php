<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Subclass;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\JoinedTableObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The subclass definition definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubClassMappingDefiner extends SubClassDefinerBase
{
    /**
     * Defines the subclass to be embedded in the parent table
     * and will store a marker value within a column of the class type.
     *
     * This is the using the single table inheritance pattern.
     *
     * **Example:**
     * <code>
     * $map->type(Player::class);
     * $map->toTable('players');
     *
     * $map->idToPrimaryKey('id');
     * $map->column('type')->asEnum(['footballer', 'cricketer', 'bowler']);
     * $map->property('name')->to('name')->asVarchar(255);
     *
     * $map->subclass()->withTypeInColumn('type', 'footballer')->define(function (MapperDefinition $map) {
     *      $map->type(Footballer::class);
     *      $map->property('club')->to('club')->asVarchar(255);
     * });
     *
     * $map->subclass()->withTypeInColumn('type', 'cricketer')->define(function (MapperDefinition $map) {
     *      $map->type(Cricketer::class);
     *      $map->property('battingAverage')->to('batting_average')->asInt();
     *
     *      $map->subclass()->withTypeInColumn('type', 'bowler')->define(function (MapperDefinition $map) {
     *           $map->type(Bowler::class);
     *           $map->property('bowlingAverage')->to('bowling_average')->asInt();
     *      });
     * });
     * </code>
     *
     * **This will create a schema as such:**
     * <code>
     * | players                                                     |
     * |-----------------|-------------------------------------------|
     * | id              | INT PRIMARY KEY                           |
     * | name            | VARCHAR(255)                              |
     * | club            | NULLABLE VARCHAR(255)                     |
     * | batting_average | NULLABLE INT                              |
     * | bowling_average | NULLABLE INT                              |
     * | type            | ENUM('footballer', 'cricketer', 'bowler') |
     * </code>
     *
     * @param string $columnName
     * @param mixed  $classTypeValue
     *
     * @return SubClassDefinitionDefiner
     */
    public function withTypeInColumn($columnName, $classTypeValue)
    {
        return new SubClassDefinitionDefiner(
                $this->orm,
                $this->parentDefinition,
                function (MapperDefinition $subClassDefinition) use ($columnName, $classTypeValue) {
                    foreach ($subClassDefinition->getColumns() as $column) {
                        $this->parentDefinition->addColumn($column->asNullable());
                    }
                    $subClassDefinition->setColumns($this->parentDefinition->getColumns());

                    call_user_func(
                            $this->callback,
                            function (Table $parentTable) use ($subClassDefinition, $columnName, $classTypeValue) {
                                $finalizedSubClassDefinition = $subClassDefinition->finalize($parentTable->getName());

                                return new EmbeddedObjectMapping(
                                        $parentTable,
                                        $finalizedSubClassDefinition,
                                        $columnName,
                                        $classTypeValue
                                );
                            },
                            $subClassDefinition
                    );
                }
        );
    }

    /**
     * Defines the of class type of the object to be determined
     * via the value of a column. This is a shortcut for a group
     * of subclasses without any extra properties. This will create
     * an ENUM column with the supplied
     *
     * This is the using the single table inheritance pattern.
     *
     * **Example:**
     * <code>
     * $map->type(Car::class);
     * $map->toTable('cars');
     *
     * $map->idToPrimaryKey('id');
     * $map->property('brand')->to('brand')->asVarchar(255);
     *
     * $map->subclass()->withTypeInColumnMap('type', [
     *      'sedan'       => SedanCar::class,
     *      'hatch'       => HatchCar::class,
     *      'convertible' => ConvertibleCar::class,
     *      'family'      => FamilyCar::class,
     * ]);
     *
     * </code>
     *
     * **This will create a schema as such:**
     * <code>
     * | players                                                  |
     * |--------|-------------------------------------------------|
     * | id     | INT PRIMARY KEY                                 |
     * | brand  | VARCHAR(255)                                    |
     * | type   | ENUM('sedan', 'hatch', 'convertible', 'family') |
     * </code>
     *
     * @param string   $columnName
     * @param string[] $columnValueClassTypeMap
     *
     * @return void
     */
    public function withTypeInColumnMap($columnName, array $columnValueClassTypeMap)
    {
        foreach ($columnValueClassTypeMap as $columnValue => $classType) {
            $this->withTypeInColumn($columnName, $columnValue)->asType($classType);
        }

        $this->parentDefinition->column($columnName)->asEnum(array_keys($columnValueClassTypeMap));
    }

    /**
     * Defines the subclass to be stored in a separate table
     * with the primary key of that table as a foreign key referencing
     * the parent table primary key.
     *
     * This is the using the class table inheritance pattern.
     *
     * **Example:**
     * <code>
     * $map->type(Player::class);
     * $map->toTable('players');
     *
     * $map->idToPrimaryKey('id');
     * $map->property('name')->to('name')->asVarchar(255);
     *
     * $map->subclass()->asSeparateTable('footballers')->define(function (MapperDefinition $map) {
     *      $map->type(Footballer::class);
     *      $map->property('club')->to('club')->asVarchar(255);
     * });
     *
     * $map->subclass()->asSeparateTable('cricketers')->define(function (MapperDefinition $map) {
     *      $map->type(Cricketer::class);
     *      $map->property('battingAverage')->to('batting_average')->asInt();
     *
     *      $map->subclass()->asSeparateTable('bowlers')->define(function (MapperDefinition $map) {
     *           $map->type(Bowler::class);
     *           $map->property('bowlingAverage')->to('bowling_average')->asInt();
     *      });
     * });
     * </code>
     *
     * **This will create a schema as such:**
     * <code>
     * | players                   |
     * |---------|-----------------|
     * | id      | INT PRIMARY KEY |
     * | name    | VARCHAR(255)    |
     *
     *
     * | footballers                                           |
     * |-------------|-----------------------------------------|
     * | id          | INT PRIMARY KEY REFERENCES (players.id) |
     * | club        | VARCHAR(255)                            |
     *
     *
     * | cricketers                                                |
     * |-----------------|-----------------------------------------|
     * | id              | INT PRIMARY KEY REFERENCES (players.id) |
     * | batting_average | INT                                     |
     *
     *
     * | bowlers                                                      |
     * |-----------------|--------------------------------------------|
     * | id              | INT PRIMARY KEY REFERENCES (cricketers.id) |
     * | bowling_average | INT                                        |
     * </code>
     *
     * @param string $tableName
     *
     * @return SubClassDefinitionDefiner
     */
    public function asSeparateTable($tableName)
    {
        return new SubClassDefinitionDefiner(
                $this->orm,
                $this->parentDefinition,
                function (MapperDefinition $subClassDefinition) use ($tableName) {

                    call_user_func(
                            $this->callback,
                            function (Table $parentTable) use ($subClassDefinition, $tableName) {
                                $subClassDefinition->idToPrimaryKey($parentTable->getPrimaryKeyColumnName());
                                $finalizedSubClassDefinition = $subClassDefinition->finalize($tableName);

                                return new JoinedTableObjectMapping(
                                        $parentTable,
                                        $finalizedSubClassDefinition
                                );
                            },
                            $subClassDefinition
                    );
                }
        );
    }
}