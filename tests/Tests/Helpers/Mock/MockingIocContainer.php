<?php

namespace Dms\Core\Tests\Helpers\Mock;

use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Ioc\IIocContainer;
use Interop\Container\ContainerInterface;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MockingIocContainer implements IIocContainer
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    protected $test;

    /**
     * MockingIocContainer constructor.
     *
     * @param \PHPUnit_Framework_TestCase $test
     */
    public function __construct(\PHPUnit_Framework_TestCase $test)
    {
        $this->test = $test;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed No entry was found for this identifier.
     * @throws \Exception
     */
    public function get($id)
    {
        if (!class_exists($id) && !interface_exists($id)) {
            throw new \Exception("Class or interface {$id} does not exist");
        }

        if ($id === ContainerInterface::class || $id === IIocContainer::class) {
            return $this;
        }

        if (interface_exists($id)) {
            return $this->test->getMockForAbstractClass($id);
        }

        $mock = $this->test->getMockBuilder($id);

        $constructor      = (new \ReflectionClass($id))->getConstructor();
        $canBeConstructed = true;
        if ($constructor) {
            $params = [];

            foreach ($constructor->getParameters() as $param) {
                if ($param->getClass()) {
                    $params[] = $this->get($param->getClass()->getName());
                } elseif ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                } else {
                    $canBeConstructed = false;
                }
            }
        }

        if ($canBeConstructed) {
            $mock->setConstructorArgs($params);
        } else {
            $mock->disableOriginalConstructor();
        }

        return $mock->getMockForAbstractClass();
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return class_exists($id) || interface_exists($id);
    }

    public function bind(string $scope, string $abstract, string $concrete)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function bindValue(string $abstract, $concrete)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function bindCallback(string $scope, string $abstract, callable $factory)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}