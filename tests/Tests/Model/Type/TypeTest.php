<?php

namespace Dms\Core\Tests\Model;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\Condition\ConditionOperatorType;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\BaseType;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\CollectionType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\Type\MixedType;
use Dms\Core\Model\Type\NotType;
use Dms\Core\Model\Type\NullType;
use Dms\Core\Model\Type\ObjectType;
use Dms\Core\Model\Type\ScalarType;
use Dms\Core\Model\Type\UnionType;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Tests\Helpers\Comparators\IgnorePropertyComparator;
use SebastianBergmann\Comparator\Factory;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeTest extends CmsTestCase
{
    private static $comparator;

    public static function setUpBeforeClass()
    {
        // Ignore validOperatorTypes as it is simply a cache
        // of the operator types and will be set only if getter
        // has been called
        self::$comparator = new IgnorePropertyComparator(BaseType::class, ['validOperatorTypes']);
        Factory::getInstance()->register(self::$comparator);
    }

    public static function tearDownAfterClass()
    {
        Factory::getInstance()->unregister(self::$comparator);
    }

    public function testEquals()
    {
        $string      = new ScalarType(IType::STRING);
        $otherString = new ScalarType(IType::STRING);
        $mixed       = new MixedType();

        $this->assertTrue($mixed->equals($mixed));
        $this->assertTrue($string->equals($string));
        $this->assertTrue($string->equals($otherString));
        $this->assertTrue($otherString->equals($string));

        $this->assertFalse($mixed->equals($string));
        $this->assertFalse($mixed->equals($otherString));
    }

    public function testIntersect()
    {
        $mixed   = new MixedType();
        $string  = new ScalarType(ScalarType::STRING);
        $int     = new ScalarType(ScalarType::INT);
        $notNull = new NotType(new NullType());

        $this->assertSame($mixed, $mixed->intersect($mixed));
        $this->assertSame($string, $string->intersect($string));
        $this->assertSame($string, $mixed->intersect($string));
        $this->assertSame($string, $string->intersect($mixed));

        $this->assertSame(null, $string->intersect($int));
        $this->assertSame($string, $string->intersect($notNull));
        $this->assertSame($notNull, $mixed->intersect($notNull));
    }

    public function testIntersectWithObjects()
    {
        $dateTime          = new ObjectType(\DateTime::class);
        $dateTimeInterface = new ObjectType(\DateTimeInterface::class);
        $anyObject         = new ObjectType();

        $this->assertSame($dateTime, $dateTime->intersect($dateTimeInterface));
        $this->assertSame($dateTime, $dateTimeInterface->intersect($dateTime));
        $this->assertSame($dateTime, $dateTime->intersect($anyObject));
    }

    public function testIntersectWithArrays()
    {
        $array          = new ArrayType(new MixedType());
        $arrayOfInts    = new ArrayType(new ScalarType(ScalarType::INT));
        $arrayOfStrings = new ArrayType(new ScalarType(ScalarType::STRING));

        $this->assertSame($arrayOfInts, $array->intersect($arrayOfInts));
        $this->assertSame(null, $arrayOfStrings->intersect($arrayOfInts));
    }

    public function testIntersectWithCollections()
    {
        $collection          = new CollectionType(new MixedType());
        $collectionOfInts    = new CollectionType(new ScalarType(ScalarType::INT));
        $collectionOfStrings = new CollectionType(new ScalarType(ScalarType::STRING));

        $this->assertSame($collectionOfInts, $collection->intersect($collectionOfInts));
        $this->assertSame(null, $collectionOfStrings->intersect($collectionOfInts));


        $concreteCollection  = new CollectionType(new MixedType(), TypedCollection::class);
        $interfaceCollection = new CollectionType(new MixedType(), ITypedCollection::class);

        $this->assertSame($concreteCollection, $concreteCollection->intersect($interfaceCollection));
    }

    public function testIntersectWithUnions()
    {
        $string         = new ScalarType(ScalarType::STRING);
        $nullableString = $string->nullable();

        $this->assertSame($nullableString, $nullableString->intersect($nullableString));
        $this->assertSame($string, $string->intersect($nullableString));
        $this->assertSame($string, $nullableString->intersect($string));
    }

    public function testIsSuperSetOf()
    {
        $mixed   = new MixedType();
        $string  = new ScalarType(ScalarType::STRING);
        $int     = new ScalarType(ScalarType::INT);
        $notNull = new NotType(new NullType());

        $this->assertTrue($mixed->isSupersetOf($string));
        $this->assertTrue($mixed->isSupersetOf($int));
        $this->assertTrue($mixed->isSupersetOf($notNull));

        $this->assertTrue($notNull->isSupersetOf($string));
        $this->assertTrue($notNull->isSupersetOf($int));

        $this->assertTrue($string->isSupersetOf($string));
        $this->assertTrue($mixed->isSupersetOf($mixed));

        $this->assertFalse($string->isSupersetOf($mixed));
        $this->assertFalse($string->isSupersetOf($notNull));
        $this->assertFalse($string->isSupersetOf($int));
        $this->assertFalse($notNull->isSupersetOf($mixed));
    }

    public function testIsSubsetOf()
    {
        $mixed   = new MixedType();
        $string  = new ScalarType(ScalarType::STRING);
        $int     = new ScalarType(ScalarType::INT);
        $notNull = new NotType(new NullType());

        $this->assertTrue($string->isSubsetOf($mixed));
        $this->assertTrue($string->isSubsetOf($notNull));
        $this->assertTrue($notNull->isSubsetOf($mixed));

        $this->assertTrue($string->isSubsetOf($string));
        $this->assertTrue($mixed->isSubsetOf($mixed));

        $this->assertFalse($string->isSubsetOf($int));
        $this->assertFalse($mixed->isSubsetOf($string));
        $this->assertFalse($mixed->isSubsetOf($int));
        $this->assertFalse($mixed->isSubsetOf($notNull));

        $this->assertFalse($notNull->isSubsetOf($string));
        $this->assertFalse($notNull->isSubsetOf($int));
    }

    public function testIsSetOfWithObjects()
    {
        $dateTime          = new ObjectType(\DateTime::class);
        $dateTimeInterface = new ObjectType(\DateTimeInterface::class);
        $anyObject         = new ObjectType();

        $this->assertTrue($dateTime->isSubsetOf($dateTimeInterface));
        $this->assertTrue($dateTime->isSubsetOf($anyObject));

        $this->assertFalse($dateTimeInterface->isSubsetOf($dateTime));
        $this->assertFalse($anyObject->isSubsetOf($dateTimeInterface));

        $this->assertFalse($dateTime->isSupersetOf($dateTimeInterface));
        $this->assertFalse($dateTime->isSupersetOf($anyObject));

        $this->assertTrue($dateTimeInterface->isSupersetOf($dateTime));
        $this->assertTrue($anyObject->isSupersetOf($dateTimeInterface));
    }

    public function testIsSetOfWithArrays()
    {
        $array       = new ArrayType(new MixedType());
        $arrayOfInts = new ArrayType(new ScalarType(ScalarType::INT));

        $this->assertTrue($arrayOfInts->isSubsetOf($array));
        $this->assertFalse($array->isSubsetOf($arrayOfInts));

        $this->assertFalse($arrayOfInts->isSupersetOf($array));
        $this->assertTrue($array->isSupersetOf($arrayOfInts));
    }

    public function testIsSetOfWithCollections()
    {
        $collection       = new CollectionType(new MixedType());
        $collectionOfInts = new CollectionType(new ScalarType(ScalarType::INT));

        $this->assertTrue($collectionOfInts->isSubsetOf($collection));
        $this->assertFalse($collection->isSubsetOf($collectionOfInts));

        $this->assertFalse($collectionOfInts->isSupersetOf($collection));
        $this->assertTrue($collection->isSupersetOf($collectionOfInts));
    }

    public function testSetsWithUnions()
    {
        $string         = new ScalarType(ScalarType::STRING);
        $nullableString = $string->nullable();

        $this->assertTrue($string->isSubsetOf($nullableString));
        $this->assertFalse($nullableString->isSubsetOf($string));

        $this->assertTrue($nullableString->isSupersetOf($string));
        $this->assertFalse($string->isSupersetOf($nullableString));
    }

    protected function makeConditionTypes(array $operatorTypeMap)
    {
        $conditions = [];

        /** @var IType[] $operatorTypeMap */
        foreach ($operatorTypeMap as $operator => $type) {
            $conditions[$operator] = new ConditionOperatorType($operator, $type);
        }

        return $conditions;
    }

    protected function commonConditionTypes(IType $type)
    {
        return $this->makeConditionTypes([
                '='                       => $type,
                '!='                      => $type,
                ConditionOperator::IN     => new ArrayType($type),
                ConditionOperator::NOT_IN => new ArrayType($type),
        ]);
    }

    public function testStringScalarType()
    {
        $type = new ScalarType(IType::STRING);

        $this->assertSame('string', $type->asTypeString());
        $this->assertSame(IType::STRING, $type->getType());
        $this->assertTrue($type->isOfType(''));
        $this->assertTrue($type->isOfType('abc'));

        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(null));
        $this->assertEquals($this->commonConditionTypes($type) + $this->makeConditionTypes([
                        ConditionOperator::STRING_CONTAINS                  => $type->nullable(),
                        ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => $type->nullable(),
                ]), $type->getConditionOperatorTypes());
    }

    public function testIntScalarType()
    {
        $type = new ScalarType(IType::INT);

        $this->assertSame('int', $type->asTypeString());
        $this->assertSame(IType::INT, $type->getType());
        $this->assertTrue($type->isOfType(123));

        $this->assertFalse($type->isOfType(123.0));
        $this->assertFalse($type->isOfType('123'));
        $this->assertFalse($type->isOfType('abc'));
        $this->assertFalse($type->isOfType(null));
        $this->assertEquals($this->commonConditionTypes($type) + $this->makeConditionTypes([
                        ConditionOperator::STRING_CONTAINS                  => Type::string()->nullable(),
                        ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => Type::string()->nullable(),
                        '>'                                                 => Type::number()->nullable(),
                        '>='                                                => Type::number()->nullable(),
                        '<'                                                 => Type::number()->nullable(),
                        '<='                                                => Type::number()->nullable(),
                ]), $type->getConditionOperatorTypes());
    }

    public function testFloatScalarType()
    {
        $type = new ScalarType(IType::FLOAT);

        $this->assertSame('float', $type->asTypeString());
        $this->assertSame(IType::FLOAT, $type->getType());
        $this->assertTrue($type->isOfType(123.0));

        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType('123'));
        $this->assertFalse($type->isOfType('abc'));
        $this->assertFalse($type->isOfType(null));
        $this->assertEquals($this->commonConditionTypes($type) + $this->makeConditionTypes([
                        ConditionOperator::STRING_CONTAINS                  => Type::string()->nullable(),
                        ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => Type::string()->nullable(),
                        '>'                                                 => Type::number()->nullable(),
                        '>='                                                => Type::number()->nullable(),
                        '<'                                                 => Type::number()->nullable(),
                        '<='                                                => Type::number()->nullable(),
                ]), $type->getConditionOperatorTypes());
    }

    public function testNull()
    {
        $type = new NullType();

        $this->assertSame('null', $type->asTypeString());
        $this->assertTrue($type->isOfType(null));

        $this->assertFalse($type->isOfType(''));
        $this->assertFalse($type->isOfType('abc'));
        $this->assertFalse($type->isOfType(123));
        $this->assertEquals($this->commonConditionTypes(new MixedType()) + $this->makeConditionTypes([
                        '>'                                                 => new MixedType(),
                        '>='                                                => new MixedType(),
                        '<'                                                 => new MixedType(),
                        '<='                                                => new MixedType(),
                        ConditionOperator::STRING_CONTAINS                  => Type::string()->nullable(),
                        ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => Type::string()->nullable(),
                ]), $type->getConditionOperatorTypes());
        $this->assertEquals(ConditionOperator::getAll(), $type->getConditionOperators());
    }

    public function testObjectTypeWithoutClass()
    {
        $type = new ObjectType();

        $this->assertSame('object', $type->asTypeString());
        $this->assertSame(null, $type->getClass());
        $this->assertTrue($type->isOfType(new \stdClass()));
        $this->assertTrue($type->isOfType(new \DateTime()));

        $this->assertFalse($type->isOfType('foo'));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(null));
        $this->assertEquals($this->commonConditionTypes($type), $type->getConditionOperatorTypes());
    }

    public function testObjectTypeWithClass()
    {
        $type = new ObjectType(\DateTimeInterface::class);

        $this->assertSame('DateTimeInterface', $type->asTypeString());
        $this->assertSame(\DateTimeInterface::class, $type->getClass());
        $this->assertTrue($type->isOfType(new \DateTime()));
        $this->assertTrue($type->isOfType(new \DateTimeImmutable()));

        $this->assertFalse($type->isOfType('foo'));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(new \stdClass()));
        $this->assertEquals($this->commonConditionTypes($type) + $this->makeConditionTypes([
                        '>'  => $type->nullable(),
                        '>=' => $type->nullable(),
                        '<'  => $type->nullable(),
                        '<=' => $type->nullable(),
                ]), $type->getConditionOperatorTypes());
    }

    public function testMixed()
    {
        $type = new MixedType();

        $this->assertSame('mixed', $type->asTypeString());
        $this->assertTrue($type->isOfType(new \DateTime()));
        $this->assertTrue($type->isOfType('foo'));
        $this->assertTrue($type->isOfType(123));
        $this->assertTrue($type->isOfType(null));
        $this->assertTrue($type->isOfType([]));
        $this->assertTrue($type->isOfType(new \stdClass()));
        $this->assertSame($type, $type->nullable());
        $this->assertSame('not<null>', $type->nonNullable()->asTypeString());
        $this->assertSame($type, $type->union(new ScalarType(IType::INT)));
        $this->assertEquals($this->commonConditionTypes($type), $type->getConditionOperatorTypes());
    }

    public function testArrayType()
    {
        $type = new ArrayType(new ScalarType(IType::INT));

        $this->assertSame('array<int>', $type->asTypeString());
        $this->assertTrue($type->isOfType([]));
        $this->assertTrue($type->isOfType([1, 2, 4]));

        $this->assertFalse($type->isOfType([1, 2, '3', 4]));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(null));
        $this->assertFalse($type->isOfType(new \stdClass()));
        $this->assertEquals($this->commonConditionTypes($type), $type->getConditionOperatorTypes());
    }

    public function testCollectionType()
    {
        $type = new CollectionType(new ObjectType(IEntity::class));

        $this->assertSame(ITypedCollection::class, $type->getCollectionClass());
        $this->assertSame(ITypedCollection::class . '<' . IEntity::class . '>', $type->asTypeString());

        $collectionMock = $this->getMockForAbstractClass(ITypedCollection::class);
        $collectionMock->method('getElementType')->willReturn(new ObjectType(IEntity::class));

        $collectionMock2 = $this->getMockForAbstractClass(ITypedCollection::class);
        $collectionMock2->method('getElementType')->willReturn(new ObjectType(\stdClass::class));

        $this->assertTrue($type->isOfType($collectionMock));

        $this->assertFalse($type->isOfType($collectionMock2));
        $this->assertFalse($type->isOfType([]));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(null));
        $this->assertFalse($type->isOfType(new \stdClass()));
        $this->assertEquals($this->commonConditionTypes($type), $type->getConditionOperatorTypes());
    }

    public function testCollectionTypeWithCustomCollectionClass()
    {
        $type = new CollectionType(new ObjectType(IEntity::class), EntityCollection::class);

        $this->assertSame(EntityCollection::class, $type->getCollectionClass());
        $this->assertSame(EntityCollection::class . '<' . IEntity::class . '>', $type->asTypeString());

        $collectionMock = new EntityCollection(IEntity::class);

        $collectionMock2 = $this->getMockForAbstractClass(ITypedCollection::class);
        $collectionMock2->method('getElementType')->willReturn(new ObjectType(IEntity::class));

        $this->assertTrue($type->isOfType($collectionMock));

        $this->assertFalse($type->isOfType($collectionMock2));
        $this->assertFalse($type->isOfType([]));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(null));
        $this->assertFalse($type->isOfType(new \stdClass()));
        $this->assertEquals($this->commonConditionTypes($type), $type->getConditionOperatorTypes());
    }

    public function testUnion()
    {
        $type = (new ScalarType(IType::FLOAT))->union(new ScalarType(IType::INT));

        $this->assertSame('float|int', $type->asTypeString());

        $this->assertTrue($type->isOfType(123.0));
        $this->assertTrue($type->isOfType(123));

        $this->assertFalse($type->isOfType('12'));
        $this->assertFalse($type->isOfType(null));
        $this->assertFalse($type->isOfType([]));
        $this->assertFalse($type->isOfType(new \stdClass()));
        $this->assertEquals($this->commonConditionTypes($type) + $this->makeConditionTypes([
                        ConditionOperator::STRING_CONTAINS                  => Type::string()->nullable(),
                        ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE => Type::string()->nullable(),
                        '>'                                                 => Type::number()->nullable(),
                        '>='                                                => Type::number()->nullable(),
                        '<'                                                 => Type::number()->nullable(),
                        '<='                                                => Type::number()->nullable(),
                ]), $type->getConditionOperatorTypes());
    }

    public function testUnionWithDuplicates()
    {
        $type = (new ScalarType(IType::FLOAT))->union(new ScalarType(IType::INT))->union(new ScalarType(IType::FLOAT));

        $this->assertSame('float|int', $type->asTypeString());
    }

    public function testUnionReturnsOriginalTypeIfOneProvided()
    {
        $type = UnionType::create([$inner = new ScalarType(IType::FLOAT)]);

        $this->assertSame($inner, $type);
        $this->assertSame('float', $type->asTypeString());
    }

    public function testUnionReturnsOriginalTypeIfApplicable()
    {
        $type = UnionType::create([new ObjectType(\DateTime::class), new ObjectType()]);

        $this->assertInstanceOf(ObjectType::class, $type);
        $this->assertSame('object', $type->asTypeString());
    }

    public function testUnionWithSame()
    {
        $type = (new ScalarType(IType::FLOAT))->union(new ScalarType(IType::FLOAT));

        $this->assertEquals(new ScalarType(IType::FLOAT), $type);
    }

    public function testNullable()
    {
        $type = (new ScalarType(IType::FLOAT))->nullable();

        $this->assertSame('float|null', $type->asTypeString());

        $this->assertTrue($type->isOfType(123.0));
        $this->assertTrue($type->isOfType(null));

        $this->assertFalse($type->isOfType([]));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(new \stdClass()));
    }

    public function testIsNullable()
    {
        $float = new ScalarType(IType::FLOAT);
        $type  = $float->nullable();

        $this->assertFalse($float->isNullable());
        $this->assertTrue($type->isNullable());
    }

    public function testNonNullable()
    {
        $float = new ScalarType(IType::FLOAT);

        $this->assertEquals($float, $float->nonNullable());
        $this->assertEquals($float, $float->nullable()->nonNullable());
    }

    public function testUnionNonNullable()
    {
        $number = (new ScalarType(IType::FLOAT))->union(new ScalarType(IType::INT));

        $this->assertEquals($number, $number->nonNullable());
        $this->assertEquals($number, $number->nullable()->nonNullable());
    }

    public function testNestedUnionFlattensTypes()
    {
        /** @var UnionType $type */
        $type = UnionType::create([
                UnionType::create([new ScalarType(IType::BOOL), new ScalarType(IType::INT)]),
                new ScalarType(IType::STRING)
        ]);

        $this->assertInstanceOf(UnionType::class, $type);
        $this->assertEquals([
                new ScalarType(IType::BOOL),
                new ScalarType(IType::INT),
                new ScalarType(IType::STRING)
        ], array_values($type->getTypes()));
    }

    public function testNot()
    {
        $type = new NotType(new ScalarType(ScalarType::INT));

        $this->assertSame('not<int>', $type->asTypeString());
        $this->assertSame('not<int>', $type->nullable()->asTypeString());
        $this->assertSame('not<int|null>', $type->nonNullable()->asTypeString());

        $this->assertTrue($type->isOfType(null));
        $this->assertTrue($type->isOfType(123.0));
        $this->assertTrue($type->isOfType(new \stdClass()));

        $this->assertFalse($type->isOfType(0));
        $this->assertFalse($type->isOfType(123));
        $this->assertFalse($type->isOfType(-4));
        $this->assertEquals($this->commonConditionTypes($type), $type->getConditionOperatorTypes());
    }
}