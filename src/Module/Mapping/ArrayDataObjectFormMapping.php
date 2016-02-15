<?php declare(strict_types = 1);

namespace Dms\Core\Module\Mapping;

use Dms\Core\Form;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Form\Object\Stage\StagedFormObject;
use Dms\Core\Model\Object\ArrayDataObject;

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