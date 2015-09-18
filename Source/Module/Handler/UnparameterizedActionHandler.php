<?php

namespace Iddigital\Cms\Core\Module\Handler;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;

/**
 * The action handler base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class UnparameterizedActionHandler extends ActionHandler implements IUnparameterizedActionHandler
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct($this->getReturnType());
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        return $this->verifyResult($this->handle());
    }

    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    abstract protected function getReturnType();

    /**
     * Runs the action handler
     *
     * @return IDataTransferObject|null
     */
    abstract public function handle();
}