<?php

namespace Iddigital\Cms\Core\Tests\Module\Handler\Fixtures;

use Iddigital\Cms\Core\Module\Handler\UnparameterizedActionHandler;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MethodActionHandler extends UnparameterizedActionHandler
{
    /**
     * @var mixed
     */
    private $return;

    /**
     * MethodActionHandler constructor.
     *
     * @param mixed $return
     */
    public function __construct($return)
    {
        $this->return = $return;
        parent::__construct();
    }

    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    protected function getReturnType()
    {
        return get_class($this->return);
    }

    /**
     * Runs the action handler
     *
     * @return DataTransferObject|null
     */
    public function handle()
    {
        return $this->return;
    }
}