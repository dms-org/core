<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\AllUniquePropertyValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AllUniquePropertyValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        $entities = new EntityCollection(TestEntity::class, [
                new TestEntity(1),
                new TestEntity(2),
                new TestEntity(3),
                new TestEntity(5),
                new TestEntity(10),
        ]);


        return new AllUniquePropertyValidator($this->processedType(), $entities, 'id');
    }

    /**
     * @return ArrayType
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::int()->nullable())->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [null],
                [[100, 99]],
                [[]],
                [range(30, 50, 2)],
                [[-102, 6]],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [[1], new Message(AllUniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
                [[1, 2], new Message(AllUniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
                [[-1, -2, null, 5], new Message(AllUniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
                [[100, 10, 10], new Message(AllUniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
        ];
    }
}