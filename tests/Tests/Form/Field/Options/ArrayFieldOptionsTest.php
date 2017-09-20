<?php

namespace Dms\Core\Tests\Form\Field\Options;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Options\FieldOption;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayFieldOptionsTest extends CmsTestCase
{
    public function testTryGetValues()
    {
        $options = new ArrayFieldOptions([
            new FieldOption(1, 'a'),
            new FieldOption(2, 'b'),
            new FieldOption(3, 'c'),
        ]);

        $this->assertSame([], $options->tryGetOptionsForValues([]));
        $this->assertEquals([new FieldOption(1, 'a')], $options->tryGetOptionsForValues([1]));
        $this->assertEquals([new FieldOption(1, 'a'), new FieldOption(3, 'c')], $options->tryGetOptionsForValues([1, 3]));
        $this->assertEquals([new FieldOption(1, 'a')], $options->tryGetOptionsForValues([1, 4]));
        $this->assertEquals([], $options->tryGetOptionsForValues([4]));
        $this->assertEquals([], $options->tryGetOptionsForValues([5]));
    }
}