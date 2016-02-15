<?php

namespace Dms\Core\Tests\Model\Criteria\Condition;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\Specification;
use Dms\Core\Model\Criteria\SpecificationDefinition;

class PersonIsOldSpecification extends Specification
{
    /**
     * Returns the class name for the object to which the specification applies.
     *
     * @return string
     */
    protected function type() : string
    {
        return Person::class;
    }

    /**
     * Defines the criteria for the specification.
     *
     * @param SpecificationDefinition $match
     *
     * @return void
     */
    protected function define(SpecificationDefinition $match)
    {
        $match->where(Person::AGE, '>=', 50);
    }
}

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConditionOperatorTest extends CmsTestCase
{
    public function testGetAll()
    {
        $this->assertInternalType('array', ConditionOperator::getAll());
        $this->assertContainsOnly('string', ConditionOperator::getAll());
        $this->assertCount(count((new \ReflectionClass(ConditionOperator::class))->getConstants()), ConditionOperator::getAll());
    }

    public function testIsValid()
    {
        $this->assertTrue(ConditionOperator::isValid(ConditionOperator::EQUALS));
        $this->assertTrue(ConditionOperator::isValid(ConditionOperator::NOT_EQUALS));
        $this->assertTrue(ConditionOperator::isValid(ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE));

        $this->assertFalse(ConditionOperator::isValid('non-existent-operator'));
    }

    public function testValidate()
    {
        ConditionOperator::validate(ConditionOperator::EQUALS);
        ConditionOperator::validate(ConditionOperator::NOT_EQUALS);
        ConditionOperator::validate(ConditionOperator::STRING_CONTAINS);

        $this->assertThrows(function () {
            ConditionOperator::validate('non-existent-operator');
        }, InvalidArgumentException::class);
    }

    public function testEqualsOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::EQUALS);

        $this->assertTrue($callable(1, 1));
        $this->assertTrue($callable(-1.0, -1.0));
        $this->assertTrue($callable('abcds', 'abcds'));
        $this->assertTrue($callable(['abcds', 1], ['abcds', 1]));
        $this->assertTrue($callable(new \stdClass(), new \stdClass()));
        $this->assertTrue($callable(null, null));

        $this->assertFalse($callable(1, '1'));
        $this->assertFalse($callable(1, 1.0));
        $this->assertFalse($callable((object)['foo' => 'bar'], (object)['foo' => 'baz']));
        $this->assertFalse($callable('adsa', 'dsvgdsb'));
    }

    public function testNotEqualsOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::NOT_EQUALS);

        $this->assertTrue($callable(1, '1'));
        $this->assertTrue($callable(1, 1.0));
        $this->assertTrue($callable('adsa', 'dsvgdsb'));
        $this->assertTrue($callable((object)['foo' => 'bar'], (object)['foo' => 'baz']));

        $this->assertFalse($callable(1, 1));
        $this->assertFalse($callable(-1.0, -1.0));
        $this->assertFalse($callable('abcds', 'abcds'));
        $this->assertFalse($callable(['abcds', 1], ['abcds', 1]));
        $this->assertFalse($callable(new \stdClass(), new \stdClass()));
        $this->assertFalse($callable(null, null));
    }

    public function testGreaterThanOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::GREATER_THAN);

        $this->assertTrue($callable(1, 0));
        $this->assertTrue($callable(122, -100));

        $this->assertFalse($callable(1, 1));
        $this->assertFalse($callable(-1.0, -1.0));
        $this->assertFalse($callable(-1, 1));
        $this->assertFalse($callable(null, 1));
        $this->assertFalse($callable(1, null));
        $this->assertFalse($callable(null, null));
    }

    public function testGreaterThanOrEqualOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::GREATER_THAN_OR_EQUAL);

        $this->assertTrue($callable(1, 0));
        $this->assertTrue($callable(122, -100));
        $this->assertTrue($callable(1, 1));
        $this->assertTrue($callable(-1.0, -1.0));

        $this->assertFalse($callable(-1, 1));
        $this->assertFalse($callable(-100, 1));
        $this->assertFalse($callable(null, 1));
        $this->assertFalse($callable(1, null));
        $this->assertFalse($callable(null, null));
    }

    public function testLessThanOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::LESS_THAN);

        $this->assertTrue($callable(-1.1, -1.0));
        $this->assertTrue($callable(-1, 1));

        $this->assertFalse($callable(1, 1));
        $this->assertFalse($callable(1, 0));
        $this->assertFalse($callable(122, -100));
        $this->assertFalse($callable(null, 1));
        $this->assertFalse($callable(1, null));
        $this->assertFalse($callable(null, null));
    }

    public function testLessThanOrEqualOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::LESS_THAN_OR_EQUAL);

        $this->assertTrue($callable(-1, 1));
        $this->assertTrue($callable(-1.0, -1.0));
        $this->assertTrue($callable(1, 1));

        $this->assertFalse($callable(1, 0));
        $this->assertFalse($callable(122, -100));
        $this->assertFalse($callable(null, 1));
        $this->assertFalse($callable(1, null));
        $this->assertFalse($callable(null, null));
    }

    public function testStringContainsOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::STRING_CONTAINS);

        $this->assertTrue($callable('abcdef', 'a'));
        $this->assertTrue($callable('abcdef', 'abc'));
        $this->assertTrue($callable('abcdef', 'def'));

        $this->assertFalse($callable('abcdef', 'A'));
        $this->assertFalse($callable('abcdef', 'aBc'));
        $this->assertFalse($callable('abcdef', '5t4y'));
        $this->assertFalse($callable('abcdef', null));
        $this->assertFalse($callable(null, '5t4y'));
        $this->assertFalse($callable(null, null));
    }

    public function testStringContainsCaseInsensitiveOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE);

        $this->assertTrue($callable('abcdef', 'a'));
        $this->assertTrue($callable('abcdef', 'abc'));
        $this->assertTrue($callable('abcdef', 'def'));
        $this->assertTrue($callable('abcdef', 'A'));
        $this->assertTrue($callable('abcdef', 'aBc'));

        $this->assertFalse($callable('abcdef', '5t4y'));
        $this->assertFalse($callable('abcdef', null));
        $this->assertFalse($callable(null, '5t4y'));
        $this->assertFalse($callable(null, null));
    }

    public function testInOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::IN);

        $this->assertTrue($callable(1, [1, 2, 3]));
        $this->assertTrue($callable(3, [1, 2, 3]));
        $this->assertTrue($callable(null, [1, null, 3]));
        $this->assertTrue($callable(new \stdClass(), [1, new \stdClass(), 3]));

        $this->assertFalse($callable(3.0, [1, 2, 3]));
        $this->assertFalse($callable(5, [1, 2, 3]));
        $this->assertFalse($callable(null, [0, 1, 2, 3]));
    }

    public function testNotInOperator()
    {
        $callable = $this->makeOperatorCallable(ConditionOperator::NOT_IN);

        $this->assertTrue($callable(3.0, [1, 2, 3]));
        $this->assertTrue($callable(5, [1, 2, 3]));
        $this->assertTrue($callable(null, [0, 1, 2, 3]));

        $this->assertFalse($callable(1, [1, 2, 3]));
        $this->assertFalse($callable(3, [1, 2, 3]));
        $this->assertFalse($callable(null, [1, null, 3]));
        $this->assertFalse($callable(new \stdClass(), [1, new \stdClass(), 3]));
    }

    /**
     * @param $operator
     *
     * @return \Closure
     * @throws \Dms\Core\Exception\NotImplementedException
     */
    protected function makeOperatorCallable($operator)
    {
        $operator = ConditionOperator::makeOperatorCallable(
                function ($l) {
                    return $l[0];
                },
                $operator,
                function ($r) {
                    return $r[1];
                }
        );

        return function ($l, $r) use ($operator) {
            return $operator([$l, $r]);
        };
    }
}