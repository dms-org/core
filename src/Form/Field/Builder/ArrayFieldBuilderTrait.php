<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\AllUniquePropertyValidator;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IFieldType;
use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\ITypedCollection;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Type\CollectionType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\TypedCollection;
use Dms\Core\Util\Debug;

/**
 * The array field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
trait ArrayFieldBuilderTrait
{
    /**
     * Validates the array has at least the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function minLength(int $length)
    {
        return $this->attr(ArrayOfType::ATTR_MIN_ELEMENTS, $length);
    }

    /**
     * Validates the array has at most the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function maxLength(int $length)
    {
        return $this->attr(ArrayOfType::ATTR_MAX_ELEMENTS, $length);
    }

    /**
     * Validates the array has the supplied amount of elements.
     *
     * @param int $length
     *
     * @return static
     */
    public function exactLength(int $length)
    {
        return $this->attr(ArrayOfType::ATTR_EXACT_ELEMENTS, $length);
    }

    /**
     * Validates that all the array elements are unique.
     *
     * @return static
     */
    public function containsNoDuplicates()
    {
        return $this->attr(ArrayOfType::ATTR_UNIQUE_ELEMENTS, true);
    }

    /**
     * Validates that all the array elements are unique within the supplied
     * set of object properties.
     *
     * @param IObjectSet $objects
     * @param string     $propertyName the property member expression
     *
     * @return static
     */
    public function allUniqueIn(IObjectSet $objects, string $propertyName)
    {
        $this->customProcessorCallbacks[] = function (IType $currentType, IFieldType $fieldType, $initialValue) use ($objects, $propertyName) {
            return new AllUniquePropertyValidator($currentType, $objects, $propertyName, $initialValue);
        };

        return $this;
    }

    /**
     * Validates that all the array elements are unique within the supplied
     * set of object properties.
     *
     * @param CollectionType $collectionType
     * @param callable|null  $mapperCallback
     * @param callable|null  $reverseMapperCallback
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function mapToCollection(CollectionType $collectionType, callable $mapperCallback = null, callable $reverseMapperCallback = null)
    {
        $collectionClass = $collectionType->getCollectionClass();

        if ($collectionClass === TypedCollection::class || $collectionClass === ITypedCollection::class) {
            $collectionFactory = function (array $input) use ($collectionType) {
                return new TypedCollection($collectionType->getElementType(), $input);
            };
        } elseif ($collectionClass === EntityIdCollection::class) {
            $collectionFactory = function (array $input) use ($collectionType) {
                return new EntityIdCollection($input);
            };
        } elseif ($collectionType->isSubsetOf(TypedObject::collectionType())) {
            $objectType = $collectionType->getElementType()->asTypeString();
            $collectionFactory = function (array $input) use ($objectType) {
                /** @var string|TypedObject $objectType */
                return $objectType::collection($input);
            };
        } else {
            throw InvalidArgumentException::format(
                'Invalid collection type supplied to %s: expecting one of (%s), %s given',
                get_class($this) . '::' . __FUNCTION__,
                Debug::formatValues([TypedCollection::class, EntityIdCollection::class, TypedObject::collectionType()->asTypeString()]),
                $collectionType->asTypeString()
            );
        }

        $this->customProcessorCallbacks[] = function (IType $currentType) use (
            $collectionType,
            $collectionFactory,
            $mapperCallback,
            $reverseMapperCallback
        ) {
            return new CustomProcessor(
                $currentType->isNullable() ? $collectionType->nullable() : $collectionType,
                function (array $elements) use ($collectionFactory, $mapperCallback) {
                    $elements = $mapperCallback ? array_map($mapperCallback, $elements) : $elements;

                    return $collectionFactory($elements);
                },
                function (ITypedCollection $collection) use ($collectionType, $reverseMapperCallback) {
                    $elements = [];

                    foreach ($collection as $key => $element) {
                        $elements[$key] = $element;
                    }

                    return $reverseMapperCallback ? array_map($reverseMapperCallback, $elements) : $elements;
                }
            );
        };

        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return static
     */
    abstract protected function attr(string $name, $value);

    /**
     * @param FieldValidator $validator
     *
     * @return static
     */
    abstract protected function validate(FieldValidator $validator);
}