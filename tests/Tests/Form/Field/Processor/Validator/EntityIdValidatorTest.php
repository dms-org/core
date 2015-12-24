<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\EntityIdValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdValidatorTest extends FieldValidatorTest
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
            ->willReturn('Entity');

        $entitiesMock->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                return $id % 3 === 0;
            }));

        return new EntityIdValidator($this->processedType(), $entitiesMock);
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
            [0],
            [3],
            [6],
            [9],
        ];
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [1, new Message(EntityIdValidator::MESSAGE, ['entity_type' => 'Entity'])],
            [2, new Message(EntityIdValidator::MESSAGE, ['entity_type' => 'Entity'])],
            [4, new Message(EntityIdValidator::MESSAGE, ['entity_type' => 'Entity'])],
            [5, new Message(EntityIdValidator::MESSAGE, ['entity_type' => 'Entity'])],
        ];
    }
}