<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Adult extends Person
{
    const PROFESSION = 'profession';

    /**
     * @var string
     */
    public $profession;

    /**
     * @inheritDoc
     */
    public function __construct($id, $firstName, $lastName, $age, $profession)
    {
        parent::__construct($id, $firstName, $lastName, $age);
        $this->profession = $profession;
    }

    /**
     * @inheritDoc
     */
    protected function defineEntity(ClassDefinition $class)
    {
        parent::defineEntity($class);

        $class->property($this->profession)->asString();
    }

    /**
     * @param int $age
     *
     * @return bool
     */
    protected function isValidAge($age)
    {
        return $age >= 18;
    }
}