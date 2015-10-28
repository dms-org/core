<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\Resequence;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\DoctrinePlatform;
use Iddigital\Cms\Core\Persistence\Db\Doctrine\IResequenceCompiler;
use Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\DoctrineTestBase;
use Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\Mocks\ConnectionMock;
use Iddigital\Cms\Core\Tests\Persistence\Db\Doctrine\Mocks\DriverMock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ResequenceCompilerTestBase extends DoctrineTestBase
{
    /**
     * @var IResequenceCompiler
     */
    protected $compiler;

    /**
     * @var ConnectionMock
     */
    protected $doctrineConnection;

    /**
     * @return AbstractPlatform
     */
    abstract protected function buildDoctrinePlatform();

    /**
     * @param DoctrinePlatform $platform
     *
     * @return IResequenceCompiler
     */
    abstract protected function buildCompiler(DoctrinePlatform $platform);

    public function setUp()
    {
        $platform = $this->buildDoctrinePlatform();

        $driver = new DriverMock();
        $driver->setDatabasePlatform($platform);
        $this->doctrineConnection = new ConnectionMock([], $driver);
        $this->compiler           = $this->buildCompiler(new DoctrinePlatform($this->doctrineConnection));
    }
}