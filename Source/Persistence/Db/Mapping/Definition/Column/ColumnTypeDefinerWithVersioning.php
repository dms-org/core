<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Column;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Locking\DateTimeVersionLockingStrategy;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Locking\IntegerVersionLockingStrategy;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Blob;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Boolean;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Date;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\DateTime;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Decimal;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Enum;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Integer;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Text;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Time;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * The column type definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnTypeDefinerWithVersioning extends ColumnTypeDefiner
{
    /**
     * @var string|null
     */
    protected $propertyName;

    /**
     * @var callable|null
     */
    protected $phpToDbConverter;

    /**
     * @var callable|null
     */
    protected $dbToPhpConverter;

    /**
     * PropertyColumnDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param callable         $callback
     * @param callable         $phpToDbConverter
     * @param callable         $dbToPhpConverter
     * @param string|null      $propertyName
     * @param string           $name
     * @param bool             $nullable
     */
    public function __construct(
            MapperDefinition $definition,
            callable $callback,
            callable $phpToDbConverter = null,
            callable $dbToPhpConverter = null,
            $propertyName,
            $name,
            $nullable = false
    ) {
        parent::__construct($definition, $callback, $name, $nullable);

        $this->phpToDbConverter = $phpToDbConverter;
        $this->dbToPhpConverter = $dbToPhpConverter;
        $this->propertyName     = $propertyName;
    }

    /**
     * Defines the column as an INTEGER type that will act as a versioning
     * column for optimistic locking. This column will be incremented every
     * time the entity is saved. If the value in the database is out of sync
     * with the value being persisted an exception will be thrown.
     *
     * @see EntityOutOfSyncException
     *
     * @return void
     */
    public function asVersionInteger()
    {
        $this->verifyHasPropertyNameForVersioning(__METHOD__);
        $this->asInt();
        $this->definition->optimisticLocking(new IntegerVersionLockingStrategy($this->propertyName, $this->name));
    }

    /**
     * Defines the column as an DATETIME type that will act as a versioning
     * column for optimistic locking. This column will be set the current UTC
     * datetime when the entity is saved. If the value in the database is out
     * of sync with the value being persisted an exception will be thrown.
     *
     * @see EntityOutOfSyncException
     *
     * @return void
     */
    public function asVersionDateTime()
    {
        $this->verifyHasPropertyNameForVersioning(__METHOD__);
        $this->asDateTime();
        $this->definition->optimisticLocking(new DateTimeVersionLockingStrategy($this->propertyName, $this->name));
    }

    private function verifyHasPropertyNameForVersioning($method)
    {
        if (!$this->propertyName) {
            throw InvalidOperationException::format(
                    'Invalid call to %s: column can only be used as a versioning column if it is mapped to property',
                    $method
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function invokeCallback(Type $type)
    {
        call_user_func($this->callback, new Column($this->name, $type), $this->phpToDbConverter, $this->dbToPhpConverter);
    }

}