<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;

/**
 * The staged form class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedForm implements IStagedForm
{
    /**
     * @var IndependentFormStage
     */
    protected $firstStage;

    /**
     * @var IFormStage[]
     */
    protected $followingStages;

    /**
     * StagedForm constructor.
     *
     * @param IndependentFormStage $firstStage
     * @param IFormStage[]         $following
     */
    public function __construct(IndependentFormStage $firstStage, array $following)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'stages', $following, IFormStage::class);

        $this->firstStage      = $firstStage;
        $this->followingStages = $following;
    }

    /**
     * @return IndependentFormStage
     */
    public function getFirstStage()
    {
        return $this->firstStage;
    }

    /**
     * @param $stageNumber
     *
     * @return IFormStage
     * @throws InvalidArgumentException
     */
    public function getStage($stageNumber)
    {
        InvalidArgumentException::verify(
                $stageNumber > 0 && $stageNumber <= $this->getAmountOfStages(),
                'Stage number must be between %s and %s, %s given', 1, $this->getAmountOfStages(), $stageNumber
        );

        if ($stageNumber === 1) {
            return $this->firstStage;
        } else {
            return $this->followingStages[$stageNumber - 2];
        }
    }

    /**
     * @return IFormStage[]
     */
    public function getFollowingStages()
    {
        return $this->followingStages;
    }

    /**
     * @return int
     */
    public function getAmountOfStages()
    {
        return 1 + count($this->followingStages);
    }

    /**
     * @inheritDoc
     */
    public function getFormForStage($stageNumber, array $previousStagesSubmission)
    {
        $stage = $this->getStage($stageNumber);

        $processedSubmission = $this->firstStage->loadForm()->process($previousStagesSubmission);

        $stageNumber--;
        for($i = 0; $i < $stageNumber - 1; $i++) {
            $processedSubmission += $this->followingStages[$i]
                    ->loadForm($processedSubmission)
                    ->process($previousStagesSubmission);
        }

        return $stage->loadForm($processedSubmission);
    }

    /**
     * {@inheritdoc]
     */
    public function process(array $submission)
    {
        $processed = $this->firstStage->loadForm()->process($submission);

        foreach ($this->followingStages as $stage) {
            $processed += $stage->loadForm($processed)->process($submission);
        }

        return $processed;
    }

    /**
     * {@inheritdoc]
     */
    public function unprocess(array $processedSubmission)
    {
        $unprocessed = [];

        /** @var IFormStage[] $reversedStages */
        $reversedStages = array_reverse($this->followingStages);

        foreach ($reversedStages as $stage) {
            $unprocessed[] = $this->unprocessStage($stage, $processedSubmission);
        }

        $unprocessed[] = $this->unprocessStage($this->firstStage, $processedSubmission);

        $unprocessedSubmission = [];
        foreach (array_reverse($unprocessed) as $unprocessedStage) {
            $unprocessedSubmission += $unprocessedStage;
        }

        return $unprocessedSubmission;
    }

    private function unprocessStage(IFormStage $stage, array $processedSubmission)
    {
        $form = $stage->loadForm($processedSubmission);

        return $form->unprocess(array_intersect_key(
                $processedSubmission,
                $form->getFields()
        ));
    }
}