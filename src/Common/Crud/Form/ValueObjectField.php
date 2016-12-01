<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Form;

use Dms\Core\Common\Crud\Definition\Form\FinalizedValueObjectFieldDefinition;
use Dms\Core\Common\Crud\Definition\Form\ValueObjectFieldDefinition;
use Dms\Core\Form\Field\Field;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Model\IValueObject;
use Dms\Core\Model\Type\Builder\Type;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ValueObjectField extends Field
{
    /**
     * @var FinalizedValueObjectFieldDefinition
     */
    private $fieldDefinition;

    /**
     * @param string            $name
     * @param string            $label
     * @param IValueObject|null $initialValue
     */
    public function __construct(string $name, string $label, IValueObject $initialValue = null)
    {
        $definition = new ValueObjectFieldDefinition();
        $this->define($definition);
        $this->fieldDefinition = $definition->finalize();

        $form = $this->fieldDefinition->getForm();

        $fieldType = new InnerFormType($form);

        $processors[] = new CustomProcessor(
            Type::object($this->fieldDefinition->getClass()->getClassName()),
            function (array $input) {
                $valueObject = $this->fieldDefinition->createNewObjectFromInput($input);
                $this->fieldDefinition->bindToObject($valueObject, $input);

                return $valueObject;
            },
            function (IValueObject $valueObject) {
                return $this->fieldDefinition->getForm()->getBinding()->getForm($valueObject)->getInitialValues();
            }
        );

        parent::__construct(
            $name,
            $label,
            $fieldType,
            $processors,
            $initialValue
        );
    }

    /**
     * Defines the structure of this value object field.
     *
     * @param ValueObjectFieldDefinition $form
     *
     * @return void
     */
    abstract protected function define(ValueObjectFieldDefinition $form);

    /**
     * @return FinalizedValueObjectFieldDefinition
     */
    final public function getFieldDefinition(): FinalizedValueObjectFieldDefinition
    {
        return $this->fieldDefinition;
    }
}