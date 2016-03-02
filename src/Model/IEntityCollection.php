<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;

/**
 * The entity collection interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IEntityCollection extends ITypedObjectCollection, IEntitySet, IMutableObjectSet
{

}
