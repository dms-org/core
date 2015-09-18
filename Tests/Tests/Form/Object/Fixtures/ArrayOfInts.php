<?php

namespace Iddigital\Cms\Core\Tests\Form\Object\Fixtures;

use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Object\DependentFormObject;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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