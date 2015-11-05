<?php

namespace Iddigital\Cms\Core\Module\Mapping;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObject;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;

/**
 * The array data object form mapping.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ArrayDataObjectFormMapping extends StagedFormDtoMapping
{
    /**
     * ArrayDataObjectFormMapping constructor.
     *
     * @param IStagedForm $stagedForm
     */
    public function __construct(IStagedForm $stagedForm)
    {
        if ($stagedForm instanceof StagedFormObject) {
            $stagedForm = clone $stagedForm;
            $stagedForm = $stagedForm->getStagedFormDefinition()->getStagedForm();
        }

        parent::__construct($stagedForm, ArrayDataObject::class);
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormSubmissionToDto(array $submission)
    {
        return new ArrayDataObject($this->getStagedForm()->process($submission));
    }
}