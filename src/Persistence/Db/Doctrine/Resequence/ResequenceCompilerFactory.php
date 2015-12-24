<?php

namespace Dms\Core\Persistence\Db\Doctrine\Resequence;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Dms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Dms\Core\Persistence\Db\Doctrine\IResequenceCompiler;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ResequenceCompilerFactory
{
    /**
     * @param DoctrinePlatform $platform
     *
     * @return IResequenceCompiler
     */
    public static function buildFor(DoctrinePlatform $platform)
    {
        $doctrinePlatform   = $platform->getDoctrinePlatform();
        $expressionCompiler = $platform->getExpressionCompiler();

        switch (true) {
            case $doctrinePlatform instanceof MySqlPlatform:
                return new MysqlResequenceCompiler($expressionCompiler);

            default:
                return new DefaultResequenceCompiler($expressionCompiler);
        }
    }
}