<?php

namespace Dms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain;

use Dms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Child extends Person
{
    const FAVOURITE_COLOUR = 'favouriteColour';

    /**
     * @var TestColour
     */
    public $favouriteColour;

    /**
     * @inheritDoc
     */
    public function __construct($id, $firstName, $lastName, $age, TestColour $favouriteColour)
    {
        parent::__construct($id, $firstName, $lastName, $age);
        $this->favouriteColour = $favouriteColour;
    }

    /**
     * @inheritDoc
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->favouriteColour)->asObject(TestColour::class);
    }

    /**
     * @param int $age
     *
     * @return bool
     */
    protected function isValidAge($age)
    {
        return $age < 18;
    }
}