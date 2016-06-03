<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Auth\IAdmin;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Form\Field\Processor\InnerCrudModuleProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Adult;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Child;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Person;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\TestColour;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\PersonModule;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerCrudModuleProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new InnerCrudModuleProcessor(
            new PersonModule(Person::collection([
                new Child(1, 'Jack', 'Baz', 15, TestColour::blue()),
                new Adult(2, 'Kate', 'Costa', 28, 'Lawyer'),
            ]), new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class)))
        );
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Person::collectionType()->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        $this->module = new PersonModule(Person::collection([
            new Child(1, 'Jack', 'Baz', 15, TestColour::blue()),
            new Adult(2, 'Kate', 'Costa', 28, 'Lawyer'),
        ]), new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class)));

        return [
            [null, null],
            [
                [
                    [
                        IObjectAction::OBJECT_FIELD_NAME => 1,
                        'first_name'                     => 'Jack',
                        'last_name'                      => 'Baz',
                        'age'                            => '10',
                        'favourite_colour'               => 'blue',
                    ],
                    [
                        'first_name'       => 'New',
                        'last_name'        => 'Kid',
                        'age'              => '15',
                        'favourite_colour' => 'yellow',
                    ],
                ],
                Person::collection([
                    new Child(1, 'Jack', 'Baz', 10, TestColour::blue()),
                    new Child(null, 'New', 'Kid', 15, TestColour::yellow()),
                ]),
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
        ];
    }
}