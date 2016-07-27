<?php declare(strict_types = 1);

namespace Dms\Core\Module\Action;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Auth\IPermission;
use Dms\Core\Form;
use Dms\Core\Module\Handler\CustomParameterizedActionHandler;
use Dms\Core\Module\IParameterizedActionHandler;
use Dms\Core\Module\IStagedFormDtoMapping;

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
        IAuthSystemInPackageContext $auth
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
    abstract protected function name() : string;

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    abstract protected function permissions() : array;

    /**
     * Gets the action form mapping.
     *
     * @return IStagedFormDtoMapping
     */
    abstract protected function formMapping() : IStagedFormDtoMapping;

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