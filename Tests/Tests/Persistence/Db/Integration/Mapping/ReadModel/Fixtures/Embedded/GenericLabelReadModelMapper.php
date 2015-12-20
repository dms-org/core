<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Embedded;

use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\ReadModel\GenericReadModelMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GenericLabelReadModelMapper extends GenericReadModelMapper
{
    /**
     * @var string
     */
    private $labelProperty;

    /**
     * GenericLabelReadModelMapper constructor.
     *
     * @param string $labelProperty
     */
    public function __construct($labelProperty)
    {
        $this->labelProperty = $labelProperty;
    }

    /**
     * Defines the read model mapping.
     *
     * @param ReadMapperDefinition $map
     *
     * @return void
     */
    protected function define(ReadMapperDefinition $map)
    {
        $map->type(GenericLabelReadModel::class);

        $map->properties([
                'id'                 => 'id',
                $this->labelProperty => 'label',
        ]);
    }
}