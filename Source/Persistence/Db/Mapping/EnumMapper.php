<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Object\Enum;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Module\InvalidHandlerClassException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Util\Debug;

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
     * @param IOrm          $orm
     * @param bool          $nullable
     * @param string        $columnName
     * @param string        $enumClass
     * @param array|null    $valueMap
     *
     * @throws InvalidArgumentException
     * @throws InvalidHandlerClassException
     */
    public function __construct(IOrm $orm, $nullable, $columnName, $enumClass, array $valueMap = null)
    {
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
        $this->enumClass = $enumClass::definition();

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

        // TODO: verify using null object mapper here is safe
        parent::__construct($orm, null);
    }

    /**
     * @return Column|null
     */
    public function getEnumValueColumn()
    {
        return $this->getDefinition()->getTable()->getColumn($this->columnName);
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

        $valueProperty = $valueProperty->to($this->columnName);

        if ($this->nullable) {
            $valueProperty = $valueProperty->nullable();
        }

        $valueProperty->asEnum($this->options);
    }
}