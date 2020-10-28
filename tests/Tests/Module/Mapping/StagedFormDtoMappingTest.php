<?php

namespace Dms\Core\Tests\Module\Mapping;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Module\IStagedFormDtoMapping;
use Dms\Core\Form\IForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class StagedFormDtoMappingTest extends CmsTestCase
{
    /**
     * @var IStagedFormDtoMapping
     */
    protected $mapping;

    /**
     * @return IStagedFormDtoMapping
     */
    abstract protected function mapping();

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->mapping = $this->mapping();
    }

    /**
     * @return IForm
     */
    abstract protected function expectedForm();

    public function testForm()
    {
        $this->assertInstanceOf(IStagedForm::class, $this->mapping->getStagedForm());
        $this->assertEquals($this->expectedForm(), $this->mapping->getStagedForm());
    }

    /**
     * @return string
     */
    abstract protected function expectedDtoType();

    public function testDtoType()
    {
        $this->assertInternalType('string', $this->mapping->getDtoType());
        $this->assertSame($this->expectedDtoType(), $this->mapping->getDtoType());
    }

    /**
     * @return array[]
     */
    abstract public function mappingTests();

    /**
     * @dataProvider mappingTests
     */
    public function testMappingDataToDto(array $data, $expectedDto)
    {
        $this->assertEquals($expectedDto, $this->mapping->mapFormSubmissionToDto($data));
    }
}