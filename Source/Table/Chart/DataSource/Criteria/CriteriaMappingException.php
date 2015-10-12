<?php

namespace Iddigital\Cms\Core\Table\Chart\DataSource\Criteria;

use Iddigital\Cms\Core\Exception\BaseException;

/**
 * The exception class for invalid criteria mappings.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMappingException extends BaseException
{
    /**
     * @param string $componentId
     *
     * @return static
     */
    public static function mustBeMappedToColumn($componentId)
    {
        return self::format(
                'Chart criteria cannot be mapped to row criteria: chart axis component \'%s\' is not mapped to a table component',
                $componentId
        );
    }
}