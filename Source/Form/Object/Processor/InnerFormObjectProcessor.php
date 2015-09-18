<?php

namespace Iddigital\Cms\Core\Form\Object\Processor;

use Iddigital\Cms\Core\Form\Field\Processor\FieldProcessor;
use Iddigital\Cms\Core\Form\Object\FormObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The inner form object processor
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormObjectProcessor extends FieldProcessor
{
    /**
     * @var FormObject
     */
    private $formObject;

    /**
     * InnerFormObjectProcessor constructor.
     *
     * @param FormObject $formObject
     */
    public function __construct(FormObject $formObject)
    {
        parent::__construct(Type::object(get_class($formObject)));
        $this->formObject = $formObject;
    }

    protected function doProcess($input, array &$messages)
    {
        return $this->formObject->submitNew($input);
    }

    protected function doUnprocess($input)
    {
        /** @var FormObject $input */
        return $this->formObject->unprocess($this->formObject->toArray());
    }
}