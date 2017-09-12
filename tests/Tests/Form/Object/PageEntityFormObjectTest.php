<?php

namespace Dms\Core\Tests\Form\Object;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\IEntity;
use Dms\Core\Tests\Form\Object\Fixtures\PageEntity;
use Dms\Core\Tests\Form\Object\Fixtures\PageEntityForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageEntityFormObjectTest extends FormObjectTest
{
    public function testConstructingWithoutEntity()
    {
        $form = new PageEntityForm();

        $this->assertSame(PageEntity::class, $form->getObjectType());
        $this->assertNull($form->getEntity());
        $this->assertNull($form->title);
        $this->assertNull($form->subTitle);
        $this->assertNull($form->content);
    }

    public function testConstructingWithEntityPopulatesForm()
    {
        $entity = new PageEntity('title', 'sub-title', 'content!');

        $form = new PageEntityForm($entity);

        $this->assertSame($entity, $form->getEntity());
        $this->assertSame('title', $form->title);
        $this->assertSame('sub-title', $form->subTitle);
        $this->assertSame('content!', $form->content);
    }


    public function testPopulatingForm()
    {
        $entity = new PageEntity('title', 'sub-title', 'content!');

        $form = new PageEntityForm();
        $form->populateForm($entity);

        $this->assertSame($entity, $form->getEntity());
        $this->assertSame('title', $form->title);
        $this->assertSame('sub-title', $form->subTitle);
        $this->assertSame('content!', $form->content);
    }

    public function testPopulatingEntity()
    {
        $form = new PageEntityForm();
        $form->submit([
                'title'     => 'title',
                'sub_title' => 'sub-title',
                'content'   => 'content!'
        ]);
        $entity = new PageEntity('', '', '');
        $form->populateEntity($entity);

        $this->assertSame('title', $entity->title);
        $this->assertSame('sub-title', $entity->subTitle);
        $this->assertSame('content!', $entity->content);
    }

    public function testInvalidEntityType()
    {
        $this->expectException(InvalidArgumentException::class);

        new PageEntityForm($this->getMockForAbstractClass(IEntity::class));
    }
}