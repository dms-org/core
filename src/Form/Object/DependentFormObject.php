<?php declare(strict_types = 1);

namespace Dms\Core\Form\Object;

use Dms\Core\Model\Object\ClassDefinition;

/**
 * The dependent form object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DependentFormObject extends FormObject
{
    public function __construct(callable $defineFormCallback)
    {
        $form = new FormObjectDefinition(new ClassDefinition($this, new \ReflectionClass(get_class($this)), __CLASS__));
        $defineFormCallback($form);
        
        parent::__construct($form->finalize(static::definition()));
    }
}