<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Condition;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\ITypedObjectCollection;
use Dms\Core\Model\ObjectCollection;
use Dms\Core\Util\Debug;

/**
 * The property condition operator enum class.
 *
 * NOTE: these operators are assumed null-safe, that is,
 * return false if one of the operands is null (unless it is null == null).
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
final class ConditionOperator
{
    const EQUALS = '=';
    const NOT_EQUALS = '!=';
    const IN = 'in';
    const NOT_IN = 'not-in';
    const STRING_CONTAINS = 'string-contains';
    const STRING_CONTAINS_CASE_INSENSITIVE = 'string-contains-case-insensitive';
    const GREATER_THAN = '>';
    const GREATER_THAN_OR_EQUAL = '>=';
    const LESS_THAN = '<';
    const LESS_THAN_OR_EQUAL = '<=';
    const ALL_SATISFIES = 'all-satisfies';
    const ANY_SATISFIES = 'any-satisfies';

    private static $operators = [
        self::EQUALS                           => true,
        self::NOT_EQUALS                       => true,
        self::IN                               => true,
        self::NOT_IN                           => true,
        self::STRING_CONTAINS                  => true,
        self::STRING_CONTAINS_CASE_INSENSITIVE => true,
        self::GREATER_THAN                     => true,
        self::GREATER_THAN_OR_EQUAL            => true,
        self::LESS_THAN                        => true,
        self::LESS_THAN_OR_EQUAL               => true,
        self::ALL_SATISFIES                    => true,
        self::ANY_SATISFIES                    => true,
    ];

    private function __construct()
    {
    }

    /**
     * @return string[]
     */
    public static function getAll() : array
    {
        return array_keys(self::$operators);
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    public static function isValid(string $operator) : bool
    {
        return isset(self::$operators[$operator]);
    }

    /**
     * @param string $operator
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validate(string $operator)
    {
        if (!isset(self::$operators[$operator])) {
            throw InvalidArgumentException::format(
                'Invalid condition operator: expecting one of (%s), \'%s\' given',
                Debug::formatValues(array_keys(self::$operators)), $operator
            );
        }
    }

    /**
     * @param callable $left
     * @param string   $operator
     * @param callable $right
     *
     * @return \Closure
     * @throws NotImplementedException
     */
    public static function makeOperatorCallable(callable $left, string $operator, callable $right)
    {
        switch ($operator) {
            case ConditionOperator::EQUALS:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    $isScalar = !(is_object($l) || is_array($l));

                    return $isScalar
                        ? $l === $r
                        : $l == $r;
                };

            case ConditionOperator::NOT_EQUALS:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    $isScalar = !(is_object($l) || is_array($l));

                    return $isScalar
                        ? $l !== $r
                        : $l != $r;
                };

            case ConditionOperator::GREATER_THAN:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    return $l > $r;
                };

            case ConditionOperator::GREATER_THAN_OR_EQUAL:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    return $l >= $r;
                };

            case ConditionOperator::LESS_THAN:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    return $l < $r;
                };

            case ConditionOperator::LESS_THAN_OR_EQUAL:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    return $l <= $r;
                };

            case ConditionOperator::STRING_CONTAINS:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    return strpos((string)$l, (string)$r) !== false;
                };

            case ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    return stripos((string)$l, (string)$r) !== false;
                };

            case ConditionOperator::IN:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($r === null) {
                        return false;
                    }

                    $type     = gettype($l);
                    $isScalar = !($type === 'object' || $type === 'array');

                    if ($isScalar) {
                        foreach ($r as $element) {
                            if ($l === $element) {
                                return true;
                            }
                        }
                    } else {
                        foreach ($r as $element) {
                            if (gettype($element) === $type && $l == $element) {
                                return true;
                            }
                        }
                    }

                    return false;
                };

            case ConditionOperator::NOT_IN:
                return function ($arg) use ($left, $right) {
                    $l = $left($arg);
                    $r = $right($arg);

                    if ($r === null) {
                        return false;
                    }

                    $type     = gettype($l);
                    $isScalar = !($type === 'object' || $type === 'array');

                    if ($isScalar) {
                        foreach ($r as $element) {
                            if ($l === $element) {
                                return false;
                            }
                        }
                    } else {
                        foreach ($r as $element) {
                            if (gettype($element) === $type && $l == $element) {
                                return false;
                            }
                        }
                    }

                    return true;
                };

            case ConditionOperator::ALL_SATISFIES:
                return function ($arg) use ($left, $right) {
                    /** @var array|\Traversable|null $l */
                    $l = $left($arg);
                    /** @var ISpecification|null $r */
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    if (!is_array($l)) {
                        $l = iterator_to_array($l);
                    }

                    return $r->isSatisfiedByAll($l);
                };

            case ConditionOperator::ANY_SATISFIES:
                return function ($arg) use ($left, $right) {
                    /** @var array|\Traversable|null $l */
                    $l = $left($arg);
                    /** @var ISpecification|null $r */
                    $r = $right($arg);

                    if ($l === null || $r === null) {
                        return false;
                    }

                    if (!is_array($l)) {
                        $l = iterator_to_array($l);
                    }

                    return $r->isSatisfiedByAny($l);
                };

            default:
                throw NotImplementedException::format(
                    'Unknown condition operator \'%s\'', $operator
                );
        }
    }
}