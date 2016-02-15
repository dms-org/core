<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Doctrine\Migration;

use Doctrine\DBAL\Types\Type;
use Dms\Core\Persistence\Db\Doctrine\Migration\Type\BaseEnumType;

/**
 * The custom enum type generator class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomEnumTypeGenerator
{
    private static $template = <<<'PHP'
namespace {namespace};

class {class} extends {base_class}
{
    protected static $values = {values};
}
PHP;


    /**
     * @param string[] $values
     *
     * @return string The doctrine type name
     */
    public static function generate(array $values) : string
    {
        $namespace = '__CustomDoctrineEnums';
        $className = 'CustomEnum__' . preg_replace('/[^a-zA-Z0-9_]/', '_', implode('__', $values));

        /** @var string|BaseEnumType $fullClassName */
        $fullClassName = $namespace . '\\' . $className;

        if (!class_exists($fullClassName, false)) {
            $class = strtr(self::$template, [
                    '{namespace}'  => $namespace,
                    '{class}'      => $className,
                    '{base_class}' => '\\' . BaseEnumType::class,
                    '{values}'     => var_export($values, true)
            ]);

            eval($class);
            Type::addType($fullClassName::getTypeName(), $fullClassName);
        }


        return $fullClassName::getTypeName();
    }
}
