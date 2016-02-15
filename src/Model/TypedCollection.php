<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;
use Dms\Core\Util\Debug;
use Pinq\Iterators\IIteratorScheme;

/**
 * The typed collection class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class TypedCollection extends Collection implements ITypedCollection
{
    /**
     * @var IType
     */
    protected $elementType;

    /**
     * @param IType                $elementType
     * @param \Traversable|array   $values
     * @param IIteratorScheme|null $scheme
     * @param Collection|null      $source
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        IType $elementType,
        $values = [],
        IIteratorScheme $scheme = null,
        Collection $source = null
    ) {
        $this->elementType = $elementType;

        foreach ($values as $value) {
            $this->verifyElement($value);
        }

        parent::__construct($values, $scheme, $source);
    }

    protected function dataToSerialize()
    {
        return $this->elementType;
    }

    protected function loadFromSerializedData($data)
    {
        return $this->elementType = $data;
    }

    protected function constructScopedSelf($elements)
    {
        return new static(Type::mixed(), $elements, $this->scheme, $this->source ?: $this);
    }

    public function getAll()
    {
        return $this->toOrderedMap()->values();
    }

    /**
     * {@inheritDoc}
     */
    final public function getElementType() : IType
    {
        return $this->elementType;
    }

    public function offsetSet($index, $value)
    {
        $this->verifyElement($value);

        parent::offsetSet($index, $value);
    }

    /**
     * @param $value
     *
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    protected function verifyElement($value)
    {
        if (!$this->elementType->isOfType($value)) {
            throw Exception\InvalidArgumentException::format(
                'Invalid element supplied to collection: expecting type of %s, %s given',
                $this->elementType->asTypeString(), Debug::getType($value)
            );
        }
    }
}
