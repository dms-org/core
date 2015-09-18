<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Core\Form\Field\Builder\BoolFieldBuilder;
use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\Field\Type\FieldType;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type as PhpType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolFieldBuilderTest extends FieldBuilderTestBase
{
    /**
     * @param string $name
     * @param string $label
     *
     * @return BoolFieldBuilder
     */
    protected function field($name = 'name', $label = 'Name')
    {
        return Field::name($name)->label($label)->bool();
    }

    public function testRequired()
    {
        $field = $this->field()->required()->build();

        $this->assertAttributes([FieldType::ATTR_REQUIRED => true], $field);

        foreach ([true, 1, '1', 'yes'] as $truthy) {
            $this->assertSame(true, $field->process($truthy));
        }

        foreach ([false, 0, 'no', '0', null] as $falsey) {
            $this->assertFieldThrows($field, $falsey, [
                    new Message(RequiredValidator::MESSAGE, [
                            'field' => 'Name',
                            'input' => $falsey,
                    ])
            ]);
        }

        $this->assertEquals(PhpType::bool(), $field->getProcessedType());
    }
}