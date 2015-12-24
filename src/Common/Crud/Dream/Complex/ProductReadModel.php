<?php

namespace Dms\Core\Common\Crud\Dream\Complex;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Model\ValueObjectCollection;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductReadModel extends ReadModel
{
    /**
     * @var string|null
     */
    public $categoryName;

    /**
     * @var int|null
     */
    public $categorySortIndex;

    /**
     * @var Product
     */
    public $product;
}