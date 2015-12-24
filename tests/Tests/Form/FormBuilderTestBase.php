<?php

namespace Dms\Core\Tests\Form;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FormBuilderTestBase extends CmsTestCase
{
    /**
     * @param array $input
     * @param IForm $form
     *
     * @return void
     */
    protected function assertProcesses(array $input, IForm $form)
    {
        $this->assertInternalType('array', $form->process($input));
    }

    /**
     * @param array $input
     * @param IForm $form
     *
     * @return InvalidFormSubmissionException
     */
    protected function assertInvalidSubmission(array $input, IForm $form)
    {
        return $this->assertThrows(function () use ($input, $form) {
            $form->process($input);
        }, InvalidFormSubmissionException::class);
    }
}