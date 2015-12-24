<?php

namespace Dms\Core\Tests\Table\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;
use Dms\Core\Table\Column\Component\Type\ColumnComponentOperator;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentOperatorTest extends CmsTestCase
{
    public function testNewOperator()
    {
        $field    = $this->mockField();
        $operator = new ColumnComponentOperator('=', $field);

        $this->assertSame('=', $operator->getOperator());
        $this->assertSame($field, $operator->getField());
    }

    public function testInvalidOperatorString()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new ColumnComponentOperator('invalid-operator', $this->mockField());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IField
     */
    private function mockField()
    {
        return $this->getMockForAbstractClass(IField::class);
    }
}