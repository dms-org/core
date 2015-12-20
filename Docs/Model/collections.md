Collections
===========

Collections classes a wrapper over standard php arrays, they provide a nice api for
interacting and performing operations on a list of values. The collections used within
this project are extensions of the [PINQ collection library][pinq] which provide a
comprehensive set of methods to aggregate or manipulate in-memory collections.

Additionally collections used within domain modelling implement the `Dms\Core\Model\ITypedCollection`
interface and are strongly typed, that is, they can only contain values of a type
which is specified upon construction.

Collections represent an in-memory list of values, objects or scalars and can
be queried using the following [api][pinq-api].

For instance a simple typed collection can be constructed as follows:

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Model\TypedCollection;
use Dms\Core\Model\Type\Builder\Type;

$numbers = new TypedCollection(Type::int(), [
    1, 2, 3, 4
]);

$numbers->sum(); // 10
$numbers
    ->select(function ($i) {
        return $i * 10;
    })
    ->sum(); // 100

$numbers[] = 5;
$numbers[] = 'abc'; // Exception, type mismatch
```

There are multiple types of collections for more specific use cases:

 - `ObjectCollection` - a list of typed objects
 - `EntityCollection` - a list of entities
 - `ValueObjectCollection` - a list of value objects

[pinq]: https://github.com/TimeToogo/Pinq
[pinq-api]: http://elliotswebsite.com/Pinq/api.html


Typed Objects
=============

Instances of `TypedObject` automatically provide a static method to construct
a collection of the called type.


```php
<?php

namespace Some\Name\Space;

$collection = StronglyTypedObject::collection([
    new StronglyTypedObject('abc'),
    new StronglyTypedObject('123'),
]);

foreach ($collection as $object) {
    echo $object->string;
}
// Should print: abc123
```

Object Sets
===========

Object sets are a narrower api that attempt to provide an abstraction between
in-memory collections and external data-stores. The minimal object set api is
described by the `Dms\Core\Model\IObjectSet` interface. This can be
queried with [criteria](./criteria.md), a common dsl that can be translated into
the commands necessary to retrieve the data.

An additional api can be provided by implementing the `IObjectSetWithLoadCriteriaSupport`
interface for providing a method to retrieve only partial objects in the form of
associative arrays instead of entire object instances.

Both these interfaces are implemented by the `ObjectCollection` class and as such
they can be used to provide a common method for retrieving data from in-memory
and external data-stores.

Entity Sets
===========

Entity sets are object sets that are specific to containing only entity objects.
They are described by the `Dms\Core\Model\IEntitySet` interface and
provide methods for loading entities by the `id` property.

This interface is implemented by the `EntityCollection` class.