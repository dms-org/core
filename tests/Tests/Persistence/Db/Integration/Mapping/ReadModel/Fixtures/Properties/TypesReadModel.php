<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Properties;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypesReadModel extends ReadModel
{
    /**
     * @var int
     */
    public $int;

    /**
     * @var float
     */
    public $float;

    /**
     * @var \DateTimeImmutable
     */
    public $date;

    /**
     * TypesReadModel constructor.
     *
     * @param int                $int
     * @param float              $float
     * @param \DateTimeImmutable $date
     */
    public function __construct($int, $float, \DateTimeImmutable $date)
    {
        parent::__construct();
        $this->int   = $int;
        $this->float = $float;
        $this->date  = $date;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->int)->asInt();

        $class->property($this->float)->asFloat();

        $class->property($this->date)->asObject(\DateTimeImmutable::class);
    }
}