<?php declare(strict_types = 1);

namespace Dms\Core\Table\Chart\DataSource\Criteria;

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
    public static function mustBeMappedToColumn(string $componentId)
    {
        return self::format(
                'Chart criteria cannot be mapped to row criteria: chart axis component \'%s\' is not mapped to a table component',
                $componentId
        );
    }
}