<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Model\IObjectSetWithIdentityByIndex;

/**
 * The object index class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectIndexOptions extends ObjectIdentityOptions
{
    /**
     * @var IObjectSetWithIdentityByIndex
     */
    protected $objects;

    /**
     * EntityIdOptions constructor.
     *
     * @param IObjectSetWithIdentityByIndex $objects
     * @param callable|null                 $labelCallback
     * @param string|null                   $labelMemberExpression
     */
    public function __construct(IObjectSetWithIdentityByIndex $objects, callable $labelCallback = null, string $labelMemberExpression = null)
    {
        parent::__construct($objects, $labelCallback, $labelMemberExpression);
    }

    /**
     * @param int    $index
     * @param object $object
     *
     * @return int
     */
    protected function getObjectIdentity(int $index, $object) : int
    {
        return $index;
    }
}