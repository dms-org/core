<?php

namespace Iddigital\Cms\Core\Module\Mapping;

use Iddigital\Cms\Core\Form;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\IStagedForm;

/**
 * The custom form dto mapping base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class CustomStagedFormDtoMapping extends StagedFormDtoMapping
{
    /**
     * @var callable
     */
    private $mappingCallback;

    /**
     * CustomFormDtoMapping constructor.
     *
     * @param IStagedForm $stagedForm
     * @param string      $dtoType
     * @param callable    $mappingCallback
     */
    public function __construct(IStagedForm $stagedForm, $dtoType, callable $mappingCallback)
    {
        parent::__construct($stagedForm, $dtoType);
        $this->mappingCallback = $mappingCallback;
    }

    /**
     * @param IForm    $form
     * @param string   $dtoType
     * @param callable $mappingCallback
     *
     * @return CustomStagedFormDtoMapping
     */
    public static function fromForm(IForm $form, $dtoType, callable $mappingCallback)
    {
        return new self($form->asStagedForm(), $dtoType, $mappingCallback);
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormSubmissionToDto(array $submission)
    {
        return call_user_func($this->mappingCallback, $this->getStagedForm()->process($submission));
    }
}