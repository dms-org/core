<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\FinalizedClassDefinition;
use Dms\Core\Module\InvalidHandlerClassException;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Type\Type;
use Dms\Core\Util\Debug;

/**
 * The enum mapper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumMapper extends ValueObjectMapper
{
    /**
     * @var FinalizedClassDefinition
     */
    private $enumClass;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var string
     */
    private $columnName;

    /**
     * @var string[]
     */
    private $options;

    /**
     * @var string
     */
    private $enumClassName;

    /**
     * @var array
     */
    private $valueMap;

    /**
     * @var array
     */
    private $flippedValueMap;

    /**
     * @var Type
     */
    private $columnType;

    /**
     * @param IOrm       $orm
     * @param bool       $nullable
     * @param string     $columnName
     * @param string     $enumClass
     * @param array|null $valueMap
     * @param Type|null  $columnType
     *
     * @throws InvalidArgumentException
     * @throws InvalidHandlerClassException
     */
    public function __construct(
        IOrm $orm,
        bool $nullable,
        string $columnName,
        string $enumClass,
        array $valueMap = null,
        Type $columnType = null
    ) {
        $this->nullable      = $nullable;
        $this->columnName    = $columnName;
        $this->enumClassName = $enumClass;
        if (!is_subclass_of($enumClass, Enum::class, true)) {
            throw InvalidHandlerClassException::format(
                'Invalid enum class: must be instance of %s, %s given',
                Enum::class,
                $enumClass
            );
        }

        /** @var Enum $enumClass */
        $this->enumClass  = $enumClass::definition();
        $this->columnType = $columnType;

        if ($valueMap !== null) {
            $options         = $enumClass::getOptions();
            $suppliedOptions = array_keys($valueMap);

            if (array_diff($options, $suppliedOptions) || array_diff($suppliedOptions, $options)) {
                throw InvalidArgumentException::format(
                    'Invalid enum value map: missing (%s) added (%s)',
                    Debug::formatValues(array_diff($options, $suppliedOptions)),
                    Debug::formatValues(array_diff($suppliedOptions, $options))
                );
            }

            $this->valueMap        = $valueMap;
            $this->flippedValueMap = array_flip($this->valueMap);
            $this->options         = $valueMap;
        } else {
            $this->options = $enumClass::getOptions();
        }

        parent::__construct($orm);
    }

    /**
     * @return Column|null
     */
    public function getEnumValueColumn()
    {
        return $this->getDefinition()->getTable()->findColumn($this->columnName);
    }

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type($this->enumClassName);

        $valueProperty = $map->property('value');

        if ($this->valueMap) {
            $valueProperty = $valueProperty
                ->mappedVia(
                    function ($phpValue) {
                        return $this->valueMap[$phpValue];
                    },
                    function ($dbValue) {
                        return $this->flippedValueMap[$dbValue];
                    }
                );
        }

        if ($this->nullable) {
            $valueProperty = $valueProperty->ignoreNullabilityTypeMismatch();
        }

        $valueProperty = $valueProperty->to($this->columnName);

        if ($this->nullable) {
            $valueProperty = $valueProperty->nullable();
        }

        if ($this->columnType) {
            $valueProperty->asType($this->columnType);
        } else {
            $valueProperty->asEnum($this->options);
        }
    }
}