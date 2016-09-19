<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Plugin;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Query\Select;

/**
 * The orm plugin interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IOrmPlugin
{
    /**
     * Hook for the defining an object mapper.
     *
     * @param IObjectMapper    $mapper
     * @param MapperDefinition $map
     *
     * @return void
     */
    public function defineMapper(IObjectMapper $mapper, MapperDefinition $map);

    /**
     * Hook for loading the SELECT query from the supplied object mapper.
     *
     * @param IObjectMapper $mapper
     * @param Select        $select
     *
     * @return void
     */
    public function loadSelect(IObjectMapper $mapper, Select $select);
}