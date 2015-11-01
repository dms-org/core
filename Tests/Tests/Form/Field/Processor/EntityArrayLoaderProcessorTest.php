<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\Field\Processor\EntityArrayLoaderProcessor;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityArrayLoaderProcessorTest extends FieldProcessorTest
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
            ->method('getAllById')
            ->will($this->returnCallback(function (array $ids) {
                $entities = [];

                foreach ($ids as $id) {
                    $entities[] = (object)['id' => $id];
                }

                return $entities;
            }));

        return new EntityArrayLoaderProcessor($entitiesMock);
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::object(\stdClass::class))->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            [[1], [(object)['id' => 1]]],
            [[1, 2, 6], [(object)['id' => 1], (object)['id' => 2], (object)['id' => 6]]],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [[$this->entityMock(1)], [1]],
            [[$this->entityMock(1), $this->entityMock(4), $this->entityMock(1234)], [1, 4, 1234]],
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