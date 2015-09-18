<?php

namespace Iddigital\Cms\Core\Form\Stage;

/**
 * The dependent form stage base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DependentFormStage extends FormStage
{
    /**
     * @var callable
     */
    protected $loadFormCallback;

    /**
     * DependentFormStage constructor.
     *
     * @param callable $loadFormCallback
     */
    public function __construct(callable $loadFormCallback)
    {
        $this->loadFormCallback = $loadFormCallback;
    }

    /**
     * @inheritDoc
     */
    public function requiresPreviousSubmission()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getForm(array $previousSubmission = null)
    {
        return call_user_func($this->loadFormCallback, $previousSubmission);
    }
}