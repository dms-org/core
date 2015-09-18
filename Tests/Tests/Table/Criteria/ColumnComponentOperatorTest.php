<?php

namespace Iddigital\Cms\Core\Tests\Table\Criteria;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Table\Column\Component\Type\ColumnComponentOperator;

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