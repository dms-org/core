<?php

namespace Iddigital\Cms\Core\Tests\Form\Object;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\ArrayValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\TypeValidator;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type as Type;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\CreatePageFormWithInnerFormField;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\KeywordForm;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\SeoFormWithInner;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreatePageWithInnerFormFieldFormObjectTest extends CmsTestCase
{
    public function testForm()
    {
        $expectedForm = Form::create()
                ->section('Page Content', [
                        Field::name('title')->label('Title')->string()->required()->maxLength(50),
                        Field::name('sub_title')->label('Sub Title')->string()->maxLength(50),
                        Field::name('content')->label('Content')->string()->required(),
                ])
                ->section('SEO', [
                        Field::name('seo')->label('Seo')->form(new SeoFormWithInner())->required(),
                ])
                ->build();

        $this->assertEquals($expectedForm, CreatePageFormWithInnerFormField::asForm());
    }

    public function testClassDefinition()
    {
        $class = CreatePageFormWithInnerFormField::formDefinition()->getClass();

        $expectedProperties = [
                'title'    => Type::string(),
                'subTitle' => Type::string()->nullable(),
                'content'  => Type::string(),
                'seo'      => Type::object(SeoFormWithInner::class),
        ];

        $this->assertEquals($expectedProperties, $class->getPropertyTypeMap());
    }

    public function testSeoClassDefinition()
    {
        $class = SeoFormWithInner::formDefinition()->getClass();

        $expectedProperties = [
                'title'       => Type::string(),
                'description' => Type::string(),
                'keywords'    => Type::arrayOf(Type::object(KeywordForm::class)),
        ];

        $this->assertEquals($expectedProperties, $class->getPropertyTypeMap());
    }

    public function testSubmittingValidForm()
    {
        $form = CreatePageFormWithInnerFormField::build([
                'title'   => 'Page Title',
                'content' => 'Hello world!',
                'seo'     => [
                        'title'       => 'Hi Google',
                        'description' => 'Checkout this page',
                        'keywords'    => [['keyword' => 'foo'], ['keyword' => 'bar'], ['keyword' => 'baz']]
                ],
        ]);

        $this->assertSame('Page Title', $form->title);
        $this->assertSame(null, $form->subTitle);
        $this->assertSame('Hello world!', $form->content);
        $this->assertInstanceOf(SeoFormWithInner::class, $form->seo);
        $this->assertSame('Hi Google', $form->seo->title);
        $this->assertSame('Checkout this page', $form->seo->description);
        $this->assertEquals([new KeywordForm('foo'), new KeywordForm('bar'), new KeywordForm('baz')], $form->seo->keywords);
    }

    public function testInvalidFormSubmission()
    {
        /** @var InvalidFormSubmissionException $exception */
        $exception = $this->assertThrows(function () {
            CreatePageFormWithInnerFormField::build([
                    'title'   => null,
                    'content' => 'Hello world!',
                    'seo'     => [
                            'title'       => false,
                            'description' => 'Checkout this page',
                            'keywords'    => 123
                    ],
            ]);
        }, InvalidFormSubmissionException::class);

        $form = $exception->getForm();
        $this->assertSame(CreatePageFormWithInnerFormField::asForm(), $form);

        $this->assertEquals([
                'title'     => [new Message(RequiredValidator::MESSAGE, ['field' => 'Title', 'input' => null])],
                'sub_title' => [],
                'content'   => [],
                'seo'       => [
                        'title'       => [new Message(RequiredValidator::MESSAGE, ['field' => 'Title', 'input' => false])],
                        'description' => [],
                        'keywords'    => [new Message(TypeValidator::MESSAGE, ['field' => 'Keywords', 'input' => 123, 'type' => 'array<array<mixed>|null>|null'])],
                ],
        ], $exception->getFieldMessageMap());
    }
}