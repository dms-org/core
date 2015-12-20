<?php

namespace Dms\Core\Common\Crud\Dream\Complex;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductImage extends ValueObject
{
    /**
     * @var string
     */
    public $filePath;

    /**
     * @var int
     */
    public $sortIndex;

    /**
     * ProductImage constructor.
     *
     * @param string $filePath
     * @param int    $sortIndex
     */
    public function __construct($filePath, $sortIndex)
    {
        parent::__construct();
        $this->filePath  = $filePath;
        $this->sortIndex = $sortIndex;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->filePath)->asString();
        $class->property($this->sortIndex)->asInt();
    }
}