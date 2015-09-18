<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Model\Type\ArrayType;
use Iddigital\Cms\Core\Model\Type\MixedType;

/**
 * The inner form value type processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormProcessor extends FieldProcessor
{
    /**
     * @var IForm
     */
    private $form;

    public function __construct(IForm $form)
    {
        parent::__construct(new ArrayType(new MixedType()));

        $this->form = $form;
    }

    protected function doProcess($input, array &$messages)
    {
        return $this->form->process($input);
    }

    protected function doUnprocess($input)
    {
        return $this->form->unprocess($input);
    }
}