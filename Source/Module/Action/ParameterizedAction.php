<?php

namespace Iddigital\Cms\Core\Module\Action;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Module\IParameterizedActionHandler;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;

/**
 * The parameterized action class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ParameterizedAction extends Action implements IParameterizedAction
{
    /**
     * @var IStagedForm
     */
    private $stagedForm;

    /**
     * @var IStagedFormDtoMapping
     */
    private $formDtoMapping;

    /**
     * {@inheritDoc}
     */
    public function __construct(
            $name,
            IAuthSystem $auth,
            array $requiredPermissions,
            IStagedFormDtoMapping $formDtoMapping,
            IParameterizedActionHandler $handler
    ) {
        parent::__construct($name, $auth, $requiredPermissions, $handler);

        if ($formDtoMapping->getDtoType() !== $handler->getDtoType()) {
            throw TypeMismatchException::format(
                    "Cannot construct %s: form dto type %s does not match handler dto type %s",
                    __METHOD__, $formDtoMapping->getDtoType() ?: 'null', $handler->getDtoType() ?: 'null'
            );
        }

        $this->stagedForm     = $formDtoMapping->getStagedForm();
        $this->formDtoMapping = $formDtoMapping;
    }


    /**
     * @return IParameterizedActionHandler
     */
    final public function getHandler()
    {
        return $this->handler;
    }

    /**
     * {@inheritDoc}
     */
    final public function getStagedForm()
    {
        return $this->stagedForm;
    }

    /**
     * @return IStagedFormDtoMapping
     */
    final public function getFormDtoMapping()
    {
        return $this->formDtoMapping;
    }

    /**
     * {@inheritDoc}
     */
    public function run(array $data)
    {
        $this->verifyUserHasPermission();

        $dto = $this->formDtoMapping->mapFormSubmissionToDto($data);

        /** @var IParameterizedActionHandler $handler */
        $handler = $this->getHandler();

        return $handler->run($dto);
    }

    /**
     * {@inheritDoc}
     */
    public function submitFirstStage(array $data)
    {
        return $this->withSubmittedFirstStage(
                $this->formDtoMapping->getStagedForm()->getFirstForm()->process($data)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function withSubmittedFirstStage(array $processedData)
    {
        $clone = clone $this;

        $clone->formDtoMapping = $clone->formDtoMapping->withSubmittedFirstStage($processedData);
        $clone->stagedForm     = $clone->formDtoMapping->getStagedForm();

        return $clone;
    }
}