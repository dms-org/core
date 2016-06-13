<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Builder\ObjectFieldBuilder;
use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Options\FieldOption;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Persistence\ArrayRepository;
use Dms\Core\Tests\Form\Field\Fixtures\TestEntity;
use Dms\Core\Tests\Form\Field\Fixtures\TestValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityFieldBuilderTest extends FieldBuilderTestBase
{
    protected function getEntities() : ArrayRepository
    {
        return new ArrayRepository(TestEntity::collection([
                new TestEntity(1, 'abc', new TestValueObject('xyz')),
                new TestEntity(2, '123', new TestValueObject('abc')),
                new TestEntity(3, 'aaa', new TestValueObject('bbb')),
        ]));
    }

    /**
     * @param string $name
     * @param string $label
     *
     * @return ObjectFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)
                ->entityFrom($this->getEntities())
                ->labelledBy(TestEntity::NAME)
                ->searchableBy(TestEntity::NAME, TestEntity::VALUE_OBJECT);
    }

    public function testSearchingOptions()
    {
        /** @var EntityIdOptions $options */
        $options = $this->field()->build()->getType()->get(FieldType::ATTR_OPTIONS);

        $this->assertSame(3, $options->count());
        $this->assertSame(true, $options->canFilterOptions());

        $this->assertEquals([
                new FieldOption(2, '123'),
                new FieldOption(1, 'abc'),
                new FieldOption(3, 'aaa'),
        ], $options->getFilteredOptions('a'));

        $this->assertEquals([
                new FieldOption(2, '123'),
                new FieldOption(1, 'abc'),
        ], $options->getFilteredOptions('abc'));

        $this->assertEquals([
                new FieldOption(2, '123'),
        ], $options->getFilteredOptions('123'));

        $this->assertEquals([
                new FieldOption(1, 'abc'),
        ], $options->getFilteredOptions('xyz'));

        $this->assertEquals([
                new FieldOption(3, 'aaa'),
        ], $options->getFilteredOptions('bbb'));

        $this->assertEquals([], $options->getFilteredOptions(''));
        $this->assertEquals([], $options->getFilteredOptions('abc123'));
    }
}