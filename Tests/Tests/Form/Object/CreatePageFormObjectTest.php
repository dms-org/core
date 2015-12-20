<?php

namespace Dms\Core\Tests\Form\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Field\Processor\Validator\ArrayValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type as Type;
use Dms\Core\Tests\Form\Object\Fixtures\CreatePageForm;
use Dms\Core\Tests\Form\Object\Fixtures\SeoForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreatePageFormObjectTest extends CmsTestCase
{
    public function testDefinition()
    {
        CreatePageForm::formDefinition();
    }

    public function testForm()
    {
        $expectedForm = Form::create()
                ->section('Page Content', [
                        Field::name('title')->label('Title')->string()->required()->maxLength(50),
                        Field::name('sub_title')->label('Sub Title')->string()->maxLength(50),
                        Field::name('content')->label('Content')->string()->required(),
                ])
                ->section('SEO Details', [
                        Field::name('seo_title')->label('Title')->string()->maxLength(50)->required(),
                        Field::name('seo_description')->label('Description')->string()->maxLength(255)->required(),
                        Field::name('seo_keywords')->label('Keywords')->arrayOf(Field::element()->string()->required()),
                ])
                ->build();

        $this->assertEquals($expectedForm, CreatePageForm::asForm());
    }

    public function testClassDefinition()
    {
        $class = CreatePageForm::formDefinition()->getClass();

        $expectedProperties = [
                'title'    => Type::string(),
                'subTitle' => Type::string()->nullable(),
                'content'  => Type::string(),
                'seo'      => Type::object(SeoForm::class),
        ];

        $this->assertEquals($expectedProperties, $class->getPropertyTypeMap());
    }

    public function testSubmittingValidForm()
    {
        $form = CreatePageForm::build([
                'title'           => 'Page Title',
                'content'         => 'Hello world!',
                'seo_title'       => 'Hi Google',
                'seo_description' => 'Checkout this page',
                'seo_keywords'    => ['foo', 'bar', 'baz'],
        ]);

        $this->assertSame('Page Title', $form->title);
        $this->assertSame(null, $form->subTitle);
        $this->assertSame('Hello world!', $form->content);
        $this->assertInstanceOf(SeoForm::class, $form->seo);
        $this->assertSame('Hi Google', $form->seo->title);
        $this->assertSame('Checkout this page', $form->seo->description);
        $this->assertSame(['foo', 'bar', 'baz'], $form->seo->keywords);
    }

    public function testInvalidFormSubmission()
    {
        /** @var InvalidFormSubmissionException $exception */
        $exception = $this->assertThrows(function () {
            CreatePageForm::build([
                    'title'           => null,
                    'content'         => 'Hello world!',
                    'seo_title'       => false,
                    'seo_description' => 'Checkout this page',
                    'seo_keywords'    => 123,
            ]);
        }, InvalidFormSubmissionException::class);

        $form = $exception->getForm();
        $this->assertSame(CreatePageForm::asForm(), $form);

        $this->assertEquals([
                'title'           => [new Message(RequiredValidator::MESSAGE, ['field' => 'Title', 'input' => null])],
                'sub_title'       => [],
                'content'         => [],
                'seo_title'       => [new Message(RequiredValidator::MESSAGE, ['field' => 'Title', 'input' => false])],
                'seo_description' => [],
                'seo_keywords'    => [new Message(TypeValidator::MESSAGE, ['field' => 'Keywords', 'input' => 123, 'type' => 'array<mixed>|null'])],
        ], $exception->getFieldMessageMap());
    }
}