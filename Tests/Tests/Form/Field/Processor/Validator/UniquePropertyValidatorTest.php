<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\UniquePropertyValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UniquePropertyValidatorTest extends FieldValidatorTest
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


        return new UniquePropertyValidator($this->processedType(), $entities, 'id');
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::int()->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [null],
                [100],
                [0],
                [45],
                [-102],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [1, new Message(UniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
                [2, new Message(UniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
                [3, new Message(UniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
                [10, new Message(UniquePropertyValidator::MESSAGE, ['property_name' => 'id'])],
        ];
    }
}