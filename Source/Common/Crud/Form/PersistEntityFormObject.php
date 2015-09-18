<?php

namespace Iddigital\Cms\Core\Common\Crud\Form;

use Iddigital\Cms\Core\Form\Object\EntityFormObject;

/**
 * The persist entity form base class.
 *
 * A form that can save or update a given entity.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class PersistEntityFormObject extends EntityFormObject implements ICreateEntityFormObject, IUpdateEntityFormObject
{
}