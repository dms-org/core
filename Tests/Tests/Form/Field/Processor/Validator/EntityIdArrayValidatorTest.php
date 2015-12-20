<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\EntityIdArrayValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdArrayValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|IEntitySet $entitiesMock */
        $entitiesMock = $this->getMockForAbstractClass(IEntitySet::class);
        $entitiesMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn('SomeEntity');

        $entitiesMock->expects($this->any())
            ->method('hasAll')
            ->will($this->returnCallback(function (array $ids) {
                return count($ids) % 3 === 0;
            }));

        return new EntityIdArrayValidator($this->processedType(), $entitiesMock);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::int())->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
            [null],
            [[]],
            [[1, 2, 3]],
            [range(1, 6)],
            [range(1, 12)],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [[1], new Message(EntityIdArrayValidator::MESSAGE, ['entity_type' => 'SomeEntity'])],
            [[1, 2], new Message(EntityIdArrayValidator::MESSAGE, ['entity_type' => 'SomeEntity'])],
            [
                range(1, 5),
                new Message(EntityIdArrayValidator::MESSAGE, ['entity_type' => 'SomeEntity'])
            ],
        ];
    }
}