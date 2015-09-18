<?php

namespace Iddigital\Cms\Core\Tests\Module\Mapping;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\IDataTransferObject;

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
    protected function setUp()
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
    public function testMappingDataToDto(array $data, IDataTransferObject $expectedDto)
    {
        $this->assertEquals($expectedDto, $this->mapping->mapFormSubmissionToDto($data));
    }
}