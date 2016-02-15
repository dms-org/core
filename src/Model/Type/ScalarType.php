<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The scalar type class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ScalarType extends BaseType
{
    private static $scalarTypes = [
            IType::STRING => 'string',
            IType::INT    => 'integer',
            IType::BOOL   => 'boolean',
            IType::FLOAT  => 'double',
    ];

    /**
     * @var string
     */
    private $type;

    public function __construct($type)
    {
        InvalidArgumentException::verify(
                isset(self::$scalarTypes[$type]),
                'The supplied type string \'%s\' is not a valid scalar type',
                $type
        );

        $this->type = $type;
        parent::__construct($type);
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function loadValidOperatorTypes() : array
    {
        $operators = parent::loadValidOperatorTypes();

        if ($this->type === self::STRING) {
            $nullableString = $this->nullable();

            $operators += [
                    ConditionOperator::STRING_CONTAINS                  => $nullableString,
                    ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => $nullableString,
            ];
        } elseif ($this->type === self::INT || $this->type === self::FLOAT) {
            $numberType = Type::number()->nullable();
            $operators += [
                    ConditionOperator::GREATER_THAN          => $numberType,
                    ConditionOperator::GREATER_THAN_OR_EQUAL => $numberType,
                    ConditionOperator::LESS_THAN             => $numberType,
                    ConditionOperator::LESS_THAN_OR_EQUAL    => $numberType,
            ];
        }

        return $operators;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value) : bool
    {
        return gettype($value) === self::$scalarTypes[$this->type];
    }
}