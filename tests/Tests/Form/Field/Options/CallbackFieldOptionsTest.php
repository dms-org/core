<?php

namespace Dms\Core\Tests\Form\Field\Options;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Options\CallbackFieldOptions;
use Dms\Core\Form\Field\Options\FieldOption;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CallbackFieldOptionsTest extends CmsTestCase
{
    public function testTryGetValues()
    {
        $options = new CallbackFieldOptions(
            function () {
                return [
                    new FieldOption('a', 'A'),
                    new FieldOption('b', 'B'),
                    new FieldOption('c', 'C'),
                ];
            },
            function ($value) {
                return new FieldOption($value, strtoupper($value));
            }
        );

        $this->assertSame([], $options->tryGetOptionsForValues([]));
        $this->assertEquals([new FieldOption('a', 'A')], $options->tryGetOptionsForValues(['a']));
        $this->assertEquals([new FieldOption('a', 'A'), new FieldOption('b', 'B'), new FieldOption('c', 'C')], $options->tryGetOptionsForValues(['a', 'b', 'c']));
    }
}