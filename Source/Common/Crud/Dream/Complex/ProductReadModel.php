<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Model\ValueObjectCollection;

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