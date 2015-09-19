<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Column;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
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
class ColumnTypeDefiner
{
    /**
     * @var MapperDefinition
     */
    private $definition;

    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var callable|null
     */
    private $phpToDbConverter;

    /**
     * @var callable|null
     */
    private $dbToPhpConverter;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var string|null
     */
    private $indexName;

    /**
     * @var bool
     */
    private $isUnique = false;

    /**
     * PropertyColumnDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param callable         $callback
     * @param callable         $phpToDbConverter
     * @param callable         $dbToPhpConverter
     * @param string           $name
     * @param bool             $nullable
     */
    public function __construct(
            MapperDefinition $definition,
            callable $callback,
            callable $phpToDbConverter = null,
            callable $dbToPhpConverter = null,
            $name,
            $nullable = false
    ) {
        $this->definition       = $definition;
        $this->callback         = $callback;
        $this->phpToDbConverter = $phpToDbConverter;
        $this->dbToPhpConverter = $dbToPhpConverter;
        $this->name             = $name;
        $this->nullable         = $nullable;
    }

    /**
     * Defines the column type as nullable.
     *
     * @return ColumnTypeDefiner
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
     * @return ColumnTypeDefiner
     */
    public function index($indexName = null)
    {
        $this->indexName = $indexName ?: $this->name . '_index';

        return $this;
    }

    /**
     * Defines an unique index constraint for the column.
     *
     * @param string|null $indexName Defaults to {column-name}_index
     *
     * @return ColumnTypeDefiner
     */
    public function unique($indexName = null)
    {
        $this->index($indexName);
        $this->isUnique = true;

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
     * Defines the column as a TINYINT column
     *
     * @return void
     */
    public function asTinyInt()
    {
        $this->asType(Integer::tiny());
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
     * Defines the column as a MEDIUMINT column
     *
     * @return void
     */
    public function asMediumInt()
    {
        $this->asType(Integer::medium());
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

        call_user_func($this->callback, new Column($this->name, $type), $this->phpToDbConverter, $this->dbToPhpConverter);

        if ($this->indexName) {
            if ($this->isUnique) {
                $this->definition->unique($this->indexName)->on($this->name);
            } else {
                $this->definition->index($this->indexName)->on($this->name);
            }
        }
    }
}