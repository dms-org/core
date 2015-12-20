<?php

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Form\Field\Builder\BoolFieldBuilder;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type as PhpType;

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