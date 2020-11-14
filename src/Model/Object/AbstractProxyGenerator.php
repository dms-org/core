<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * The abstract proxy generator class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AbstractProxyGenerator
{
    private static $count = 0;

    /**
     * @var object[]
     */
    private static $proxyCache = [];

    /**
     * @param string $class
     *
     * @return object
     */
    public static function createProxyInstance(string $class)
    {
        if (!isset(self::$proxyCache[$class])) {
            $reflection = new \ReflectionClass($class);

            if (!$reflection->isAbstract()) {
                return $reflection->newInstanceWithoutConstructor();
            }

            $proxyNamespace = '__Proxies';
            $proxyName      = str_replace('\\', '_', $reflection->getName()) . self::$count++;
            $baseClass      = $class;
            $methods        = [];

            foreach ($reflection->getMethods(\ReflectionMethod::IS_ABSTRACT) as $method) {
                $methods[] = self::buildMethod($method);
            }

            $methods = implode(PHP_EOL, $methods);

            $classString = <<<PHP
namespace {$proxyNamespace};

class {$proxyName} extends \\{$baseClass} {
    {$methods}
}
PHP;
            eval($classString);
            self::$proxyCache[$class] = (new \ReflectionClass($proxyNamespace . '\\' . $proxyName))->newInstanceWithoutConstructor();
        }

        return clone self::$proxyCache[$class];
    }

    protected static function buildMethod(\ReflectionMethod $method)
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = implode(' ', [
                self::getParameterTypeHint($parameter),
                $parameter->isPassedByReference() ? '&' : '',
                '$' . $parameter->getName(),
                $parameter->isOptional() ? '=' : '',
                $parameter->isOptional() ? var_export($parameter->getDefaultValue(), true) : '',
            ]);
        }

        return implode(' ', [
            $method->isProtected() ? 'protected' : '',
            $method->isPublic() ? 'public' : '',
            $method->returnsReference() ? '&' : '',
            'function',
            $method->getName(),
            '(' . implode(', ', $parameters) . ')',
            $method->hasReturnType() ? ' : ' . self::getReturnType($method) : '',
            '{}',
        ]);
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    protected static function getReturnType(\ReflectionMethod $method) : string
    {
        return self::convertReflectedTypeToCode($method->getReturnType(), $method->getDeclaringClass());
    }
    /**
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    protected static function getParameterTypeHint(\ReflectionParameter $parameter) : string
    {
        if ($parameter->hasType()) {
            return self::convertReflectedTypeToCode($parameter->getType(), $parameter->getClass());
        } else {
            return '';
        }
    }

    protected static function convertReflectedTypeToCode(\ReflectionNamedType $reflection, \ReflectionClass $context = null): string
    {
        $type = $reflection->getName();

        if ($type === "self" && $context) {
            $type = $context->getName();
        }

        if (!$reflection->isBuiltin()) {
            $type = '\\' . $type;
        }

        if (
            $reflection->allowsNull()
            && $type[0] !== '?'
            && version_compare(PHP_VERSION, '7.1.0', '>=')) {
            return '?' . $type;
        }

        return $type;
    }
}