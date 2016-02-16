<?php declare(strict_types = 1);

namespace Dms\Core\Module\Mapping;

use Dms\Core\Form;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Module\IStagedFormDtoMapping;

/**
 * The form dto mapping base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class StagedFormDtoMapping implements IStagedFormDtoMapping
{
    /**
     * @var IStagedForm
     */
    protected $stagedForm;

    /**
     * @var string
     */
    protected $dtoType;

    /**
     * FormDtoMapping constructor.
     *
     * @param IStagedForm $form
     * @param string      $dtoType
     */
    public function __construct(IStagedForm $form, string $dtoType)
    {
        $this->stagedForm = $form;
        $this->dtoType    = $dtoType;
    }

    /**
     * {@inheritdoc}
     */
    final public function getStagedForm() : IStagedForm
    {
        return $this->stagedForm;
    }

    /**
     * {@inheritdoc}
     */
    final public function getDtoType() : string
    {
        return $this->dtoType;
    }

    /**
     * @inheritDoc
     */
    public function withSubmittedFirstStage(array $processedFirstStageData)
    {
        $clone = clone $this;

        $clone->stagedForm = $clone->stagedForm->withSubmittedFirstStage($processedFirstStageData);

        return $clone;
    }
}