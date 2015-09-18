<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\Field\Processor\EntityLoaderProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityLoaderProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|IEntitySet $entitiesMock */
        $entitiesMock = $this->getMockForAbstractClass(IEntitySet::class);

        $entitiesMock->expects($this->any())
                ->method('getElementType')
                ->willReturn(Type::object(\stdClass::class));

        $entitiesMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) {
                return (object)['id' => $id];
            }));

        return new EntityLoaderProcessor($entitiesMock);
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
            [1, (object)['id' => 1]],
            [2, (object)['id' => 2]],
            [3, (object)['id' => 3]],
            [100, (object)['id' => 100]],
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