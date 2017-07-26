<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\ObjectArrayLoaderProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectArrayLoaderProcessorWithEntityCollectionTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new ObjectArrayLoaderProcessor(Entity::collection([
            $this->entityMock(1),
            $this->entityMock(2),
            $this->entityMock(3),
            $this->entityMock(4),
        ]));
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Entity::type())->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            [[1], [$this->entityMock(1)]],
            [[1, 2, 4], [$this->entityMock(1), $this->entityMock(2), $this->entityMock(4)]],
        ];
    }

    public function testFailsToProcessIncorrectId()
    {
        $this->expectException(EntityNotFoundException::class);
        $messages = [];
        $this->processor->process([1, 2, 5], $messages);
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [[$this->entityMock(1)], [1]],
            [[$this->entityMock(1), $this->entityMock(4), $this->entityMock(1234)], [1, 4]],
        ];
    }

    protected function entityMock($id)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Entity $entityMock */
        $entityMock = $this->createMock(Entity::class);

        $entityMock->setId($id);

        return $entityMock;
    }
}