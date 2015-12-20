<?php

namespace Dms\Core\Common\Crud\Dream\Complex;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductWithinCategory extends Entity
{
    /**
     * @var int
     */
    public $sortIndex;

    /**
     * ProductWithinCategory constructor.
     *
     * @param Product $product
     * @param int     $sortIndex
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Product $product, $sortIndex)
    {
        if (!$product->hasId()) {
            throw new InvalidArgumentException('Product must have id');
        }

        parent::__construct($product->getId());
        $this->sortIndex = $sortIndex;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->sortIndex)->asInt();
    }
}