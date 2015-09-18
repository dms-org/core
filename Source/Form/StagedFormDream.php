<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Persistence\IRepository;

/** @var IRepository $someRepo */
$form = StagedForm::begin(
        Form::create()
                ->section('First Stage', [
                        Field::name('category')
                                ->label('Category')
                                ->entityIdFrom($someRepo)
                ])
)->then(function (array $data) use ($someRepo) {
    return Form::create()
            ->section('Second Stage', [
                    Field::name('sub_category')
                            ->label('Subcategory')
                            ->entitiesFrom(new EntityCollection(SubCategory::class, $someRepo->matching(
                                    SubCategory::criteria()->where('categoryId', '=', $data['category'])
                            )))
                            ->required()
            ]);
})->build();

$firstForm  = $form->getFirstStage()->loadForm();
$secondForm = $form->getStage(2)->loadForm($firstForm->process(['category' => 2]));
$secondForm->process(['sub_category' => 12]);
$form->unprocess(['category' => 2, 'sub_category' => 12]);