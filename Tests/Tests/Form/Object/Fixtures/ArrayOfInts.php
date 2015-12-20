<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Object\DependentFormObject;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfInts extends DependentFormObject
{
    /**
     * @var int[]
     */
    public $data;

    /**
     * ArrayOfInts constructor.
     *
     * @param int $length
     */
    public function __construct($length)
    {
        parent::__construct(function ($form) use ($length) {
            $this->defineForm($form, $length);
        });
    }

    public static function withLength($length)
    {
        return new self($length);
    }

    protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->data)->asArrayOf(Type::int());
    }

    protected function defineForm(FormObjectDefinition $form, $length)
    {
        $form->section('Numbers', [
            //
            $form->field($this->data)
                    ->name('data')
                    ->label('Numbers')
                    ->arrayOf(Field::element()->int()->required())
                    ->exactLength($length)
                    ->required(),
        ]);
    }
}