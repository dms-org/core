<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception;

/**
 * The custom specification class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomSpecification extends Specification
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var callable
     */
    protected $definitionCallback;

    /**
     * CustomSpecification constructor.
     *
     * @param string   $type
     * @param callable $definitionCallback
     */
    public function __construct(string $type, callable $definitionCallback)
    {
        $this->type               = $type;
        $this->definitionCallback = $definitionCallback;
        parent::__construct();
    }

    /**
     * Returns the class name for the object to which the specification applies.
     *
     * @return string
     */
    protected function type() : string
    {
        return $this->type;
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
        call_user_func($this->definitionCallback, $match);
    }
}