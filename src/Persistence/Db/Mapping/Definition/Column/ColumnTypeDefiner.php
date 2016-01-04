<?php

namespace Dms\Core\Persistence\Db\Mapping\Definition\Column;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Type\Blob;
use Dms\Core\Persistence\Db\Schema\Type\Boolean;
use Dms\Core\Persistence\Db\Schema\Type\Date;
use Dms\Core\Persistence\Db\Schema\Type\DateTime;
use Dms\Core\Persistence\Db\Schema\Type\Decimal;
use Dms\Core\Persistence\Db\Schema\Type\Enum;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Text;
use Dms\Core\Persistence\Db\Schema\Type\Time;
use Dms\Core\Persistence\Db\Schema\Type\Type;
use Dms\Core\Persistence\Db\Schema\Type\Varchar;

/**
 * The column type definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnTypeDefiner
{
    /**
     * @var MapperDefinition
     */
    protected $definition;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $nullable;

    /**
     * @var string|null
     */
    protected $indexName;

    /**
     * @var bool
     */
    protected $isUnique = false;

    /**
     * PropertyColumnDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param callable         $callback
     * @param string           $name
     * @param bool             $nullable
     */
    public function __construct(
            MapperDefinition $definition,
            callable $callback,
            $name,
            $nullable = false
    ) {
        $this->definition = $definition;
        $this->callback   = $callback;
        $this->name       = $name;
        $this->nullable   = $nullable;
    }

    /**
     * Defines the column type as nullable.
     *
     * @return static
     */
    public function nullable()
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * Defines an index for the column.
     *
     * @param string|null $indexName Defaults to {column-name}_index
     *
     * @return static
     */
    public function index($indexName = null)
    {
        $this->indexName = $indexName ?: $this->name . '_index';

        return $this;
    }

    /**
     * Defines an unique index constraint for the column.
     *
     * @param string|null $indexName Defaults to {column-name}_unique_index
     *
     * @return static
     */
    public function unique($indexName = null)
    {
        $this->indexName = $indexName ?: $this->name . '_unique_index';
        $this->isUnique  = true;

        return $this;
    }


    /**
     * Defines the column as the supplied type
     *
     * @param Type $type
     *
     * @return void
     */
    public function type(Type $type)
    {
        $this->asType($type);
    }

    /**
     * Defines the column as a VARCHAR column
     *
     * @param int $length
     *
     * @return void
     */
    public function asVarchar($length)
    {
        $this->asType(new Varchar($length));
    }

    /**
     * Defines the column as a BOOLEAN column
     *
     * @return void
     */
    public function asBool()
    {
        $this->asType(new Boolean());
    }

    /**
     * Defines the column as a INT column
     *
     * @return void
     */
    public function asInt()
    {
        $this->asType(Integer::normal());
    }

    /**
     * Defines the column as an unsigned INT column
     *
     * @return void
     */
    public function asUnsignedInt()
    {
        $this->asType(Integer::normal()->unsigned());
    }

    /**
     * Defines the column as a TINYINT column
     *
     * @return void
     */
    public function asTinyInt()
    {
        $this->asType(Integer::tiny());
    }

    /**
     * Defines the column as an unsigned TINYINT column
     *
     * @return void
     */
    public function asUnsignedTinyInt()
    {
        $this->asType(Integer::tiny()->unsigned());
    }

    /**
     * Defines the column as a SMALLINT column
     *
     * @return void
     */
    public function asSmallInt()
    {
        $this->asType(Integer::small());
    }

    /**
     * Defines the column as an unsigned SMALLINT column
     *
     * @return void
     */
    public function asUnsignedSmallInt()
    {
        $this->asType(Integer::small()->unsigned());
    }

    /**
     * Defines the column as a MEDIUMINT column
     *
     * @return void
     */
    public function asMediumInt()
    {
        $this->asType(Integer::medium());
    }

    /**
     * Defines the column as an unsigned MEDIUMINT column
     *
     * @return void
     */
    public function asUnsignedMediumInt()
    {
        $this->asType(Integer::medium()->unsigned());
    }

    /**
     * Defines the column as a BIGINT column
     *
     * @return void
     */
    public function asBigInt()
    {
        $this->asType(Integer::big());
    }

    /**
     * Defines the column as an unsigned BIGINT column
     *
     * @return void
     */
    public function asUnsignedBigInt()
    {
        $this->asType(Integer::big()->unsigned());
    }

    /**
     * Defines the column as a DECIMAL column
     *
     * @param int $totalPrecision
     * @param int $decimalPoints
     *
     * @return void
     */
    public function asDecimal($totalPrecision, $decimalPoints = 0)
    {
        $this->asType(new Decimal($totalPrecision, $decimalPoints));
    }

    /**
     * Defines the column as a DATE column
     *
     * @return void
     */
    public function asDate()
    {
        $this->asType(new Date());
    }

    /**
     * Defines the column as a TIME column
     *
     * @return void
     */
    public function asTime()
    {
        $this->asType(new Time());
    }

    /**
     * Defines the column as a DATETIME column
     *
     * @return void
     */
    public function asDateTime()
    {
        $this->asType(new DateTime());
    }

    /**
     * Defines the column as a ENUM column
     *
     * @param string[] $options
     *
     * @return void
     */
    public function asEnum(array $options)
    {
        $this->asType(new Enum($options));
    }

    /**
     * Defines the column as a TEXT column
     *
     * @return void
     */
    public function asText()
    {
        $this->asType(Text::normal());
    }

    /**
     * Defines the column as a SMALLTEXT column
     *
     * @return void
     */
    public function asSmallText()
    {
        $this->asType(Text::small());
    }

    /**
     * Defines the column as a MEDIUMTEXT column
     *
     * @return void
     */
    public function asMediumText()
    {
        $this->asType(Text::medium());
    }

    /**
     * Defines the column as a LONGTEXT column
     *
     * @return void
     */
    public function asLongText()
    {
        $this->asType(Text::long());
    }

    /**
     * Defines the column as a BLOB column
     *
     * @return void
     */
    public function asBlob()
    {
        $this->asType(Blob::normal());
    }

    /**
     * Defines the column as a SMALLBLOB column
     *
     * @return void
     */
    public function asSmallBlob()
    {
        $this->asType(Blob::small());
    }

    /**
     * Defines the column as a MEDIUMBLOB column
     *
     * @return void
     */
    public function asMediumBlob()
    {
        $this->asType(Blob::medium());
    }

    /**
     * Defines the column as a LONGBLOB column
     *
     * @return void
     */
    public function asLongBlob()
    {
        $this->asType(Blob::long());
    }

    /**
     * @param Type $type
     *
     * @return void
     */
    public function asType(Type $type)
    {
        if ($this->nullable) {
            $type = $type->nullable();
        }

        $this->invokeCallback($type);

        if ($this->indexName) {
            if ($this->isUnique) {
                $this->definition->unique($this->indexName)->on($this->name);
            } else {
                $this->definition->index($this->indexName)->on($this->name);
            }
        }
    }

    /**
     * @param Type $type
     *
     * @return void
     */
    protected function invokeCallback(Type $type)
    {
        call_user_func($this->callback, new Column($this->name, $type));
    }
}