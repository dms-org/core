<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Child extends Person
{
    const FAVOURITE_COLOUR = 'favouriteColour';

    /**
     * @var Colour
     */
    public $favouriteColour;

    /**
     * @inheritDoc
     */
    public function __construct($id, $firstName, $lastName, $age, Colour $favouriteColour)
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

        $class->property($this->favouriteColour)->asObject(Colour::class);
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