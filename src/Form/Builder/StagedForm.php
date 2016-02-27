<?php declare(strict_types = 1);

namespace Dms\Core\Form\Builder;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IFormStage;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Stage\DependentFormStage;
use Dms\Core\Form\Stage\IndependentFormStage;
use Dms\Core\Form\StagedForm as ActualStagedForm;
use Dms\Core\Util\Debug;
use Dms\Core\Util\Reflection;

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
        $this->firstStage = $firstStage;
        if ($previous) {
            $this->followingStages = $previous->followingStages;
        }
    }

    /**
     * @return ActualStagedForm
     */
    public function build() : ActualStagedForm
    {
        return new ActualStagedForm($this->firstStage, $this->followingStages);
    }

    /**
     * Constructs a staged from from a generator function.
     * This function must yield the form for each stage and
     * the previously submitted data will be sent.
     *
     * Example:
     * <code>
     * $stagedFrom = StagedFrom::create(2, function () {
     *      $data = (yield Form::create()
     *              ->section('First Stage', [...]));
     *
     *      $data = (yield Form::create()
     *              ->section('Second Stage', [...]));
     * });
     * </code>
     *
     * @param int      $numberOfStages
     * @param callable $formStagesGeneratorFunction
     *
     * @return ActualStagedForm
     * @throws InvalidArgumentException
     */
    public static function generator(int $numberOfStages, callable $formStagesGeneratorFunction) : ActualStagedForm
    {
        $generator = $formStagesGeneratorFunction();

        if (!($generator instanceof \Generator)) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: first argument must be a generator function, function returned %s instead',
                    Debug::getType($generator)
            );
        }

        $builder                 = self::begin($generator->current());
        $numberOfDependentStages = $numberOfStages - 1;

        for ($i = 1; $i <= $numberOfDependentStages; $i++) {
            // TODO: Don't recreate generator for every form stage. (low-pri)
            $builder->then(function (array $data) use ($formStagesGeneratorFunction, $i) {
                /** @var \Generator $generator */
                $generator = $formStagesGeneratorFunction();
                for ($current = 1; $current <= $i; $current++) {
                    $generator->send($data);
                }

                return $generator->current();
            });
        }

        return $builder->build();
    }

    /**
     * @param IForm|Form|callable $firstStageForm
     *
     * @return StagedForm
     * @throws InvalidArgumentException
     */
    public static function begin($firstStageForm) : StagedForm
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
     * @param IStagedForm $form
     *
     * @return StagedForm
     */
    public static function fromExisting(IStagedForm $form) : StagedForm
    {
        $self = new self($form->getFirstStage());

        $self->followingStages = $form->getFollowingStages();

        return $self;
    }

    /**
     * @param IForm|Form|callable $formStage
     * @param string[]            $fieldNamesDefinedInStage Only required for fields that are depended on.
     *
     * @return StagedForm
     * @throws InvalidArgumentException
     */
    public function then($formStage, array $fieldNamesDefinedInStage = []) : StagedForm
    {
        $this->followingStages[] = self::parseStage($formStage, null, $fieldNamesDefinedInStage);

        return $this;
    }

    /**
     * @param string[] $fieldNames
     * @param callable $formStage
     * @param string[] $fieldNamesDefinedInStage
     *
     * @return StagedForm
     * @throws InvalidArgumentException
     */
    public function thenDependingOn(array $fieldNames, callable $formStage, array $fieldNamesDefinedInStage = []) : StagedForm
    {
        $this->followingStages[] = self::parseStage($formStage, $fieldNames, $fieldNamesDefinedInStage);

        return $this;
    }

    /**
     * Embeds the supplied staged form within the current staged form.
     *
     * @param IStagedForm $embeddedForm
     *
     * @return StagedForm
     */
    public function embed(IStagedForm $embeddedForm) : StagedForm
    {
        foreach ($embeddedForm->getAllStages() as $stage) {
            $this->followingStages[] = $stage;
        }

        return $this;
    }

    /**
     * Embeds the form stage within the current staged form.
     *
     * @param IFormStage $stage
     *
     * @return StagedForm
     */
    public function embedStage(IFormStage $stage) : StagedForm
    {
        $this->followingStages[] = $stage;

        return $this;
    }

    /**
     * @param IForm|Form|callable $formStageArgument
     * @param string[]|null       $requiredFieldNames
     * @param string[]            $fieldNamesDefinedInStage
     *
     * @return DependentFormStage|IndependentFormStage
     * @throws InvalidArgumentException
     */
    protected static function parseStage($formStageArgument, array $requiredFieldNames = null, array $fieldNamesDefinedInStage = [])
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
                return new DependentFormStage($formStageArgument, $fieldNamesDefinedInStage, $requiredFieldNames);
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