<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\ObjectLoaderProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectLoaderProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|IIdentifiableObjectSet $entitiesMock */
        $entitiesMock = $this->getMockForAbstractClass(IIdentifiableObjectSet::class);

        $entitiesMock->expects($this->any())
            ->method('getElementType')
            ->willReturn(Type::object(\stdClass::class));

        $entitiesMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) {
                return $this->mockEntity($id);
            }));

        $entitiesMock->expects($this->any())
            ->method('getObjectId')
            ->will($this->returnCallback(function (IEntity $entity) {
                return $entity->getId();
            }));

        return new ObjectLoaderProcessor($entitiesMock);
    }

    protected function mockEntity($id) : IEntity
    {
        $entity = $this->getMockForAbstractClass(IEntity::class);

        $entity->method('getId')->willReturn($id);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::object(\stdClass::class)->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            [1, $this->mockEntity(1)],
            [2, $this->mockEntity(2)],
            [3, $this->mockEntity(3)],
            [100, $this->mockEntity(100)],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [$this->entityMock(1), 1],
            [$this->entityMock(253), 253],
        ];
    }

    protected function entityMock($id)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|IEntity $entityMock */
        $entityMock = $this->getMockForAbstractClass(IEntity::class);

        $entityMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);

        return $entityMock;
    }
}