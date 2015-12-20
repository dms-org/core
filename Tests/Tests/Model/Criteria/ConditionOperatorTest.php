<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ConditionOperatorTest extends CmsTestCase
{
    public function testAllOperators()
    {
        $operators = ConditionOperator::getAll();

        $this->assertEquals(array_values((new \ReflectionClass(ConditionOperator::class))->getConstants()), $operators);
    }

    public function testIsValid()
    {
        $operators = ConditionOperator::getAll();

        foreach ($operators as $operator) {
            $this->assertTrue(ConditionOperator::isValid($operator));
        }

        $this->assertFalse(ConditionOperator::isValid('some-invalid-operator'));
    }

    public function testValidate()
    {
        $operators = ConditionOperator::getAll();

        foreach ($operators as $operator) {
            ConditionOperator::validate($operator);
        }

        $this->assertThrows(function () {
            ConditionOperator::validate('some-invalid-operator');
        }, InvalidArgumentException::class);
    }
}