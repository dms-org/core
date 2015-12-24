Objects
=======

The core package introduces a set of classes to help implement strongly typed domain models.

All typed classes extend `Dms\Core\Model\Object\TypedObject`.
For instance:

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

class StronglyTypedObject extends TypedObject
{
    /**
     * @var string
     */
    public $string;

    /**
     * @param string $string
     */
    public function __construct($string)
    {
        parent::__construct(); // NOTE: this line is important
        $this->string = $string;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    final protected function define(ClassDefinition $class)
    {
        $class->property($this->string)->asString();
    }
}
```

The class can be defined similar to standard PHP classes but must additionally specify
the expected types of values contained within the class properties within the `define`
method. If the property is every set to a value of the wrong type, an exception will
be thrown.

For instance the following code will act as follows:
```php
<?php

namespace Some\Name\Space;

$object = new StronglyTypedObject('abc'); // Ok
$object = new StronglyTypedObject(123); // Exception
```

You can define a property as any of the php primitive types or even a particular class.
The types must be exact or an exception will be thrown. If a property is optional, you
can call `$class->property($this->string)->nullable()->asString()` to make the property
accept `NULL` as a valid value. Properties can also be defined as immutable, that is, its
value cannot be changed once it is set, using `$class->property($this->string)->immutable()->asString()`.
An exception will be thrown when an immutable property is changed.

Domain Models
=============
The `TypedObject` class is the basis for all strongly typed objects and often should not be used
directly. When implementing a domain model, there are more descriptive base classes available:

 - `Dms\Core\Model\Object\Entity` - For classes containing an integer id property,
    they are uniquely identifiable, eg a `Product` class would be an entity.
 - `Dms\Core\Model\Object\ValueObject` - For classes which simply wrap a value,
    for instance a `Money` class, it only contains a value. *NOTE:* Classes extending `ValueObject`
    are automatically immutable, their properties cannot be changed once set.
 - `Dms\Core\Model\Object\Enum` - For classes representing a value from particular
   set of options, eg a `Colour` enum may contain the options red, green or blue.
 - `Dms\Core\Model\Object\DataTransferObject` - Or DTO for short, are classes which
   simply contain data to pass around within the application.
 - `Dms\Core\Model\Object\ReadModel` - For classes which contain a subset of the data
   of another class. For instance, if you only needed `name` and `price` properties of a `Product`
   entity from the repository, you could create a read model containing just those properties.

Example Entity
==============

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Person extends Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @inheritDoc
     */
    public function __construct($name = '')
    {
        parent::__construct();
        $this->name = $name;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->name)->asString();
    }
}

$person = new Person('Joe');
$person->getId(); // NULL
$person->setId(5);
$person->getId(); // 5
$person->setId(10); // Exception: id has already been set
$person->name = 'Jack';
```

Example Value Object
====================

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Exception\InvalidArgumentException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Money extends ValueObject
{
    /**
     * @var int
     */
    public $cents;

    /**
     * @inheritDoc
     */
    public function __construct($cents = 0)
    {
        InvalidArgumentException::verify($cents >= 0, 'cents cannot be less than 0, %d given', $cents);

        parent::__construct();
        $this->cents = $cents;
    }

    /**
     * @param int $dollars
     * @return Money
     */
    public static function dollars($dollars)
    {
        return new Money((int)round($dollars * 100));
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->cents)->asInt();
    }

    /**
     * @param Money $money
     * @return Money
     */
    public function add(Money $money)
    {
        return new Money($this->cents + $money->cents);
    }
}

$aDollar           = new Money(100);
$fiveDollars       = Money::dollars(5);
$sixDollars        = $fiveDollars->add($aDollar);
$sixDollars->cents = 10; // Exception: object is immutable.
```


Example Enum
============

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Object\PropertyTypeDefiner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Colour extends Enum
{
    const RED = 'red';
    const GREEN = 'green';
    const BLUE = 'blue';

    /**
     * Defines the type of the options contained within the enum.
     *
     * @param PropertyTypeDefiner $values
     *
     * @return void
     */
    protected function defineEnumValues(PropertyTypeDefiner $values)
    {
        $values->asString();
    }
}

$red = new Colour(Colour::RED);
$green = new Colour(Colour::GREEN);
$blue = new Colour(Colour::BLUE);
$red->getValue(); // 'red'
$red->is($red); // true
$red->is('red'); // true
$red->is($blue); // false
Colour::getOptions(); // ['red', 'green', 'blue']
Colour::isValid('green'); // true
$invalid = new Colour('invalid-colour'); // Exception: invalid enum option
```
