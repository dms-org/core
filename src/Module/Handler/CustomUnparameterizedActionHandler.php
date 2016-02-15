<?php declare(strict_types = 1);

namespace Dms\Core\Module\Handler;

use Dms\Core\Form;
use Dms\Core\Model\IDataTransferObject;
use Dms\Core\Module\IUnparameterizedActionHandler;

/**
 * The custom action handler base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class CustomUnparameterizedActionHandler extends UnparameterizedActionHandler implements IUnparameterizedActionHandler
{
    /**
     * @var callable
     */
    private $handleCallback;

    /**
     * @var string|null
     */
    private $returnType;

    /**
     * CustomActionHandler constructor.
     *
     * @param callable    $handleCallback
     * @param string|null $returnType
     *
     * @throws \Dms\Core\Exception\InvalidArgumentException
     */
    public function __construct(callable $handleCallback, string $returnType = null)
    {
        $this->handleCallback = $handleCallback;
        $this->returnType     = $returnType;
        parent::__construct();
    }

    /**
     * Gets the return dto type of the action handler.
     *
     * @return string|null
     */
    protected function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Runs the action handler
     *
     * @return IDataTransferObject|null
     */
    public function handle()
    {
        return call_user_func($this->handleCallback);
    }
}