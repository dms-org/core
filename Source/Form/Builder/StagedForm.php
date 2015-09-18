<?php

namespace Iddigital\Cms\Core\Form\Builder;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\IFormStage;
use Iddigital\Cms\Core\Form\Stage\DependentFormStage;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
use Iddigital\Cms\Core\Util\Debug;
use Iddigital\Cms\Core\Util\Reflection;
use Iddigital\Cms\Core\Form\StagedForm as ActualStagedForm;

/**
 * The staged form builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedForm
{
    /**
     * @var IndependentFormStage
     */
    protected $firstStage;

    /**
     * @var IFormStage[]
     */
    protected $followingStages = [];

    /**
     * StagedForm constructor.
     *
     * @param IndependentFormStage $firstStage
     * @param StagedForm           $previous
     */
    final protected function __construct(IndependentFormStage $firstStage, StagedForm $previous = null)
    {
        $this->firstStage      = $firstStage;
        if ($previous) {
            $this->followingStages = $previous->followingStages;
        }
    }

    /**
     * @return ActualStagedForm
     */
    public function build()
    {
        return new ActualStagedForm($this->firstStage, $this->followingStages);
    }

    /**
     * @param IForm|Form|callable $firstStageForm
     *
     * @return StagedForm
     * @throws InvalidArgumentException
     */
    public static function begin($firstStageForm)
    {
        $firstStage = self::parseStage($firstStageForm);

        if (!($firstStage instanceof IndependentFormStage)) {
            throw InvalidArgumentException::format(
                    'Invalid form stage argument: first stage must be independent, callable with required parameter given'
            );
        }

        return new self($firstStage);
    }

    /**
     * @param IForm|Form|callable $formStage
     *
     * @return StagedForm
     * @throws InvalidArgumentException
     */
    public function then($formStage)
    {
        $this->followingStages[] = self::parseStage($formStage);

        return $this;
    }

    /**
     * @param IForm|Form|callable $formStageArgument
     *
     * @return DependentFormStage|IndependentFormStage
     * @throws InvalidArgumentException
     */
    protected static function parseStage($formStageArgument)
    {
        if ($formStageArgument instanceof Form) {
            $formStageArgument = $formStageArgument->build();
        }

        if ($formStageArgument instanceof IForm) {
            return new IndependentFormStage($formStageArgument);
        }

        if (is_callable($formStageArgument)) {
            $requiredParameters = Reflection::fromCallable($formStageArgument)->getNumberOfRequiredParameters();

            if ($requiredParameters === 0) {
                $form = $formStageArgument();

                if ($form instanceof Form) {
                    $form = $form->build();
                }

                return new IndependentFormStage($form);
            } elseif ($requiredParameters === 1) {
                return new DependentFormStage($formStageArgument);
            }

            throw InvalidArgumentException::format(
                    'Invalid form stage argument: callable must have 0 or 1 required parameters, %s given',
                    $requiredParameters
            );
        }

        throw InvalidArgumentException::format(
                'Invalid form stage argument: expecting %s|%s|%s, %s given',
                IForm::class, Form::class, 'callable', Debug::getType($formStageArgument)
        );
    }
}