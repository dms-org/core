<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Mode\IdentifyingRelationMode;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Relation\ToOneRelation;

/**
 * The embedded value object table definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedValueObjectTableDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $foreignKeyToParent;

    public function __construct(callable $callback, $tableName)
    {
        $this->callback = $callback;
        $this->tableName = $tableName;
    }

    /**
     * Sets the column name of the foreign key on the
     * related table.
     *
     * @param string $columnName
     *
     * @return void
     */
    public function withParentIdAs($columnName)
    {
        $this->foreignKeyToParent = $columnName;
    }

    public function using(IEmbeddedObjectMapper $mapper)
    {
        call_user_func($this->callback, function () use ($mapper) {
            return new ToOneRelation(
                    new ToOneRelationObjectReference($mapper),
                    $this->foreignKeyToParent,
                    new IdentifyingRelationMode()
            );
        });
    }
}