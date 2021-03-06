<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\OrderingDirection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OrderingDirectionTest extends CmsTestCase
{
    public function testAllOperators()
    {
        $operators = OrderingDirection::getAll();

        $this->assertEquals(array_values((new \ReflectionClass(OrderingDirection::class))->getConstants()), $operators);
    }

    public function testIsValid()
    {
        $directions = OrderingDirection::getAll();

        foreach ($directions as $direction) {
            $this->assertTrue(OrderingDirection::isValid($direction));
        }

        $this->assertFalse(OrderingDirection::isValid('some-invalid-operator'));
    }

    public function testValidate()
    {
        $directions = OrderingDirection::getAll();

        foreach ($directions as $operator) {
            OrderingDirection::validate($operator);
        }

        $this->assertThrows(function () {
            OrderingDirection::validate('some-invalid-direction');
        }, InvalidArgumentException::class);
    }
}