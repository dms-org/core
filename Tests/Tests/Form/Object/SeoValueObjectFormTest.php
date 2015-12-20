<?php

namespace Dms\Core\Tests\Form\Object;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IValueObject;
use Dms\Core\Tests\Form\Object\Fixtures\SeoValueObject;
use Dms\Core\Tests\Form\Object\Fixtures\SeoValueObjectForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SeoValueObjectFormTest extends FormObjectTest
{
    public function testConstructingWithoutValueObject()
    {
        $form = new SeoValueObjectForm();

        $this->assertSame(SeoValueObject::class, $form->getObjectType());
        $this->assertNull($form->title);
        $this->assertNull($form->description);
        $this->assertNull($form->keywords);
    }

    public function testConstructingWithValueObjectPopulatesForm()
    {
        $valueObject = new SeoValueObject('title', 'desc', ['key', 'word']);

        $form = new SeoValueObjectForm($valueObject);
        $this->assertSame('title', $form->title);
        $this->assertSame('desc', $form->description);
        $this->assertSame(['key', 'word'], $form->keywords);
    }


    public function testPopulatingForm()
    {
        $entity = new SeoValueObject('title', 'desc', ['key', 'word']);

        $form = new SeoValueObjectForm();
        $form->populateForm($entity);

        $this->assertSame('title', $form->title);
        $this->assertSame('desc', $form->description);
        $this->assertSame(['key', 'word'], $form->keywords);
    }

    public function testPopulatingValueObject()
    {
        $form = new SeoValueObjectForm();
        $form->submit([
                'title'       => 'title',
                'description' => 'desc',
                'keywords'    => ['key', 'word']
        ]);

        /** @var SeoValueObject $valueObject */
        $valueObject = $form->populateValueObject();

        $this->assertSame('title', $valueObject->title);
        $this->assertSame('desc', $valueObject->description);
        $this->assertSame(['key', 'word'], $valueObject->keywords);
    }

    public function testInvalidValueObjectType()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new SeoValueObjectForm($this->getMockForAbstractClass(IValueObject::class));
    }
}