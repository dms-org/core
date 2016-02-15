<?php declare(strict_types = 1);

namespace Dms\Core\Module\Mapping;

use Dms\Core\Form;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IStagedForm;

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
    public function __construct(IStagedForm $stagedForm, string $dtoType, callable $mappingCallback)
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
    public static function fromForm(IForm $form, string $dtoType, callable $mappingCallback) : CustomStagedFormDtoMapping
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