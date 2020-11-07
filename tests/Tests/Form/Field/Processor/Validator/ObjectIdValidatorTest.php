<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\ObjectIdValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectIdValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|IIdentifiableObjectSet $entitiesMock */
        $entitiesMock = $this->getMockForAbstractClass(IIdentifiableObjectSet::class);
        $entitiesMock->expects($this->any())
            ->method('getObjectType')
            ->willReturn('Entity');

        $entitiesMock->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($id) {
                return $id % 3 === 0;
            }));

        return new ObjectIdValidator($this->processedType(), $entitiesMock);
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
            [1, new Message(ObjectIdValidator::MESSAGE, ['object_type' => 'Entity'])],
            [2, new Message(ObjectIdValidator::MESSAGE, ['object_type' => 'Entity'])],
            [4, new Message(ObjectIdValidator::MESSAGE, ['object_type' => 'Entity'])],
            [5, new Message(ObjectIdValidator::MESSAGE, ['object_type' => 'Entity'])],
        ];
    }
}