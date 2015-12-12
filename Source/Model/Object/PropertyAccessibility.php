<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception;

/**
 * The property accessibility class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyAccessibility
{
    const ACCESS_PUBLIC = 'public';
    const ACCESS_PROTECTED = 'protected';
    const ACCESS_PRIVATE = 'private';

    /**
     * @var string
     */
    private $accessibility;

    /**
     * @var string
     */
    private $declaredClass;

    public function __construct($accessibility, $declaredClass)
    {
        Exception\InvalidArgumentException::verify(
                in_array($accessibility, [self::ACCESS_PRIVATE, self::ACCESS_PROTECTED, self::ACCESS_PUBLIC], true),
                'The accessibility string must be a valid option'
        );

        $this->accessibility = $accessibility;
        $this->declaredClass = $declaredClass;
    }

    /**
     * Constructs the property accessibility from the supplied reflection.
     *
     * @param \ReflectionProperty $reflection
     *
     * @return PropertyAccessibility
     */
    public static function from(\ReflectionProperty $reflection)
    {
        if ($reflection->isPublic()) {
            $accessibility = self::ACCESS_PUBLIC;
        } elseif ($reflection->isProtected()) {
            $accessibility = self::ACCESS_PROTECTED;
        } else {
            $accessibility = self::ACCESS_PRIVATE;
        }

        return new self($accessibility, $reflection->getDeclaringClass()->getName());
    }

    /**
     * @return string
     */
    public function getAccessibility()
    {
        return $this->accessibility;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->accessibility === self::ACCESS_PUBLIC;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->accessibility === self::ACCESS_PRIVATE;
    }

    /**
     * @return string
     */
    public function getDeclaredClass()
    {
        return $this->declaredClass;
    }

    /**
     * Returns whether the property is accessible from the supplied class
     * or null if not in a class scope.
     *
     * @param string|null $class
     *
     * @return bool
     */
    public function isAccessibleFrom($class)
    {
        switch ($this->accessibility) {
            case self::ACCESS_PUBLIC:
                return true;

            case self::ACCESS_PROTECTED:
                return is_a($class, $this->declaredClass, true) || is_a($this->declaredClass, $class, true);

            case self::ACCESS_PRIVATE:
                return $class === $this->declaredClass;
        }
    }
}