<?php

namespace Table\DataSource\Criteria;

use Dms\Core\Exception\BaseException;

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
    public static function mustBeMappedToProperty($componentId)
    {
        return self::format(
                'Row criteria cannot be mapped to object criteria: column component \'%s\' is not mapped to a class property',
                $componentId
        );
    }
}