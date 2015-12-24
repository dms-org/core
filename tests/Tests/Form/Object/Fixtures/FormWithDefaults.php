<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\IndependentFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormWithDefaults extends IndependentFormObject
{
    /**
     * @var string[]
     */
    public $terms = ['foo', 'bar', 'baz'];

    /**
     * @var \DateTimeImmutable
     */
    public $eventDate;

    /**
     * @var SubFormWithDefaults
     */
    public $inner;

    protected function defineForm(FormObjectDefinition $form)
    {
        $this->eventDate = new \DateTimeImmutable('2015-01-01');

        $form->section('Words', [
            //
            $form->field($this->terms)
                    ->name('terms')
                    ->label('Words')
                    ->arrayOf(Field::element()->string()->required())
                    ->required(),
            //
            $form->field($this->eventDate)
                    ->name('event_date')
                    ->label('Event Date')
                    ->date('Y-m-d')
                    ->required(),
        ]);

        $form->bind($this->inner)->to(new SubFormWithDefaults());
    }
}