Criteria
========

Criteria objects provide a common language (dsl) for querying a set of objects.
This allows for implementing logic which can be processed against both in-memory
collections and external data-stores via the `IObjectSet` interface.

Criteria supports filter conditions, ordering and slicing of collections to
retrieve the desired objects.

Member Expressions
==================

Member expressions are a small dsl designed to make it simple to traverse an object graph.

```php
<?php

namespace Some\Name\Space;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\ObjectCollection;

class StronglyTypedObject extends TypedObject
{
    /**
     * @var AnotherTypedObject
     */
    public $object;

    /**
     * @var ObjectCollection|AnotherTypedObject[]
     */
    public $objects;

    // ...
}

class AnotherTypedObject extends TypedObject
{
    /**
     * @var int
     */
    public $number;

    /**
     * @var ObjectCollection|ThirdTypedObject[]
     */
    public $thirdObjects;

    // ...
}

class ThirdTypedObject extends TypedObject
{
    /**
     * @var string
     */
    public $string;

    // ...
}

$collection = StronglyTypedObject::collection([/* ... */]);

$results = $collection->matching(
    $collection->criteria()
            ->where('object.number', '=', 10) // Compare nested properties
            ->where('objects.count()', '=', 3) // Count collections
            ->where('objects.sum(number)', '>', 2) // Sum collections
            ->where('objects.average(number)', '>', 3.0) // Average collections
            ->where('objects.max(number)', '<', 2) // Find the maximum
            ->where('objects.min(number)', '!=', 3) // Find the minimum
            ->where('objects.flatten(thirdObjects).count()', '!=', 3) // Flatten nested collections
);
```

## Loading relationships via repositories

There are also special methods for dealing with entities and relationships using integer id properties:

```php
<?php

namespace Some\Name\Space;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\EntityIdCollection;

class RootEntity extends Entity
{
    /**
     * @var int
     */
    public $relatedId;

    /**
     * @var EntityIdCollection|int[]
     */
    public $relatedIdCollection;

    // ...
}

class RelatedEntity extends Entity
{
    /**
     * @var int
     */
    public $number;

    // ...
}

/** @var IRepository|IObjectSetWithLoadCriteriaSupport $repository */
$repository = some_repository();

$results = $repository->matching(
    $repository->criteria()
            ->where('load(relatedId).number', '=', 10) // Load related entities
            ->where('loadAll(relatedIdCollection).sum(number)', '>', 3) // Load related entity collections
);

foreach ($results as $entity) {
    var_dump($entity->relatedId, $entity->relatedIdCollection);
}
```

Load Criteria
=============

Load criteria is an extension of criteria which also uses member expressions to specify what
data from a particular entity to load. This is supported if the collection implements the
`IObjectSetWithLoadCriteriaSupport` interface.

```php
<?php

namespace Some\Name\Space;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Persistence\IRepository;
use Iddigital\Cms\Core\Model\IObjectSetWithLoadCriteriaSupport;

class RootEntity extends Entity
{
    /**
     * @var string
     */
    public $data;

    /**
     * @var int
     */
    public $relatedId;

    /**
     * @var EntityIdCollection|int[]
     */
    public $relatedIdCollection;

    // ...
}

class RelatedEntity extends Entity
{
    /**
     * @var int
     */
    public $number;

    // ...
}

/** @var IRepository|IObjectSetWithLoadCriteriaSupport $repository */
$repository = some_repository();

$results = $repository->loadMatching(
    $repository->loadCriteria()
            ->loadAll([
                'data'                                     => 'data-index',
                'load(relatedId)'                          => 'related-entity',
                'loadAll(relatedIdCollection).sum(number)' => 'sum-of-numbers',
            ])
            ->whereStringContains('data', 'abc')
);

foreach ($results as $data) {
    var_dump($data['data-index'], $data['related-entity'], $data['sum-of-numbers']);
}
```

Specifications
==============

A natural extension of criteria is the [specification pattern][spec-pattern]. This allows
you to encapsulate a criteria condition as class allowing specific conditions to be
reused without logic duplication.

Specification classes extend from `Iddigital\Cms\Core\Model\Criteria\Specification` or
must implement the `Iddigital\Cms\Core\Model\ISpecification` interface.

They share the member expression / condition dsl such that specifications can be used
for both in-memory collections and external data-stores.

Example:

```php
<?php

namespace Some\Name\Space;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Criteria\Specification;
use Iddigital\Cms\Core\Model\Criteria\SpecificationDefinition;

class Person extends Entity
{
    const AGE = 'age';

    /**
     * @var int
     */
    public $age;

    public function __construct($age)
    {
        parent::__construct();
        $this->age = $age;
    }

    // ...
}

class PersonIsOldSpecification extends Specification
{
    /**
     * Returns the class name for the object to which the specification applies.
     *
     * @return string
     */
    protected function type()
    {
        return Person::class;
    }

    /**
     * Defines the criteria for the specification.
     *
     * @param SpecificationDefinition $match
     *
     * @return void
     */
    protected function define(SpecificationDefinition $match)
    {
        $match->where(Person::AGE, '>=', 50);
    }
}

$spec = new PersonIsOldSpecification();

$spec->isSatisfiedBy(new Person(15)); // false
$spec->isSatisfiedBy(new Person(60)); // true

// Convert the specification to an equivalent criteria
$criteria = $spec->asCriteria();
```

You can also use the specification within an existing criteria using the
`whereStatifies($specification)` method.

[spec-pattern]: https://en.wikipedia.org/wiki/Specification_pattern
