<?php

namespace Iddigital\Cms\Core\Module\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Module\Handler\CustomParameterizedActionHandler;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;

/**
 * The self-handling parameterized action class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class SelfHandlingParameterizedAction extends ParameterizedAction
{
    /**
     * @inheritDoc
     */
    public function __construct(
            IAuthSystem $auth
    ) {
        $formDtoMapping = $this->formMapping();

        parent::__construct(
                $this->name(),
                $auth,
                $this->permissions(),
                $formDtoMapping,
                new CustomParameterizedActionHandler(
                        function ($data) {
                            return $this->runHandler($data);
                        },
                        $this->returnType(),
                        $formDtoMapping->getDtoType()
                )
        );
    }

    /**
     * Gets the action name.
     *
     * @return string
     */
    abstract protected function name();

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    abstract protected function permissions();

    /**
     * Gets the action form mapping.
     *
     * @return IStagedFormDtoMapping
     */
    abstract protected function formMapping();

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    abstract protected function returnType();

    /**
     * @inheritDoc
     */
    final public function getParameterTypeClass()
    {
        /** @var IParameterizedActionHandler $handler */
        $handler = $this->getHandler();

        return $handler->getParameterTypeClass();
    }

    /**
     * Runs the action handler.
     *
     * @param object $data
     *
     * @return object|null
     */
    abstract protected function runHandler($data);
}