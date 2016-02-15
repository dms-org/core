<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

/**
 * The with elements type class.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class WithElementsType extends BaseType
{
    /**
     * @var IType
     */
    protected $elementType;

    public function __construct(IType $elementType, $typeString)
    {
        $this->elementType = $elementType;
        parent::__construct($typeString);
    }

    /**
     * @param IType $type
     *
     * @return bool
     */
    protected function checkThisIsSubsetOf(IType $type) : bool
    {
        $class = get_called_class();
        if ($type instanceof $class) {
            /** @var static $type */
            return $this->elementType->isSubsetOf($type->elementType);
        }

        return parent::checkThisIsSubsetOf($type);
    }

    /**
     * Gets the element type.
     *
     * @return IType
     */
    public function getElementType() : IType
    {
        return $this->elementType;
    }
}