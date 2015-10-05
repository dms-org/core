<?php

namespace Iddigital\Cms\Core\Module\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\IUnparameterizedAction;
use Iddigital\Cms\Core\Module\IUnparameterizedActionHandler;

/**
 * The unparameterized action class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class UnparameterizedAction extends Action implements IUnparameterizedAction
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
            $name,
            IAuthSystem $auth,
            array $requiredPermissions,
            IUnparameterizedActionHandler $handler
    ) {
        parent::__construct($name, $auth, $requiredPermissions, $handler);
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->verifyUserHasPermission();

        /** @var IUnparameterizedActionHandler $handler */
        $handler = $this->getHandler();

        return $handler->run();
    }
}