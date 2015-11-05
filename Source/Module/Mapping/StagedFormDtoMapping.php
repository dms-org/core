<?php

namespace Iddigital\Cms\Core\Module\Mapping;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;

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
    private $stagedForm;

    /**
     * @var string
     */
    private $dtoType;

    /**
     * FormDtoMapping constructor.
     *
     * @param IStagedForm $form
     * @param string      $dtoType
     */
    public function __construct(IStagedForm $form, $dtoType)
    {
        $this->stagedForm = $form;
        $this->dtoType    = $dtoType;
    }

    /**
     * {@inheritdoc}
     */
    final public function getStagedForm()
    {
        return $this->stagedForm;
    }

    /**
     * {@inheritdoc}
     */
    final public function getDtoType()
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