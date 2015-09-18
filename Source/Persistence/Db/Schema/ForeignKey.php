<?php

namespace Iddigital\Cms\Core\Persistence\Db\Schema;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Type\IType;

/**
 * The foreign key class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKey
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $localColumnNames;

    /**
     * @var string
     */
    private $referencedTableName;

    /**
     * @var string[]
     */
    private $referencedColumnNames;

    /**
     * @see ForeignKeyMode
     * @var string
     */
    private $onDeleteMode;

    /**
     * @see ForeignKeyMode
     * @var string
     */
    private $onUpdateMode;

    /**
     * ForeignKey constructor.
     *
     * @param string   $name
     * @param string[] $localColumnNames
     * @param string   $referencedTableName
     * @param string[] $referencedColumnNames
     * @param string   $onDeleteMode
     * @param string   $onUpdateMode
     */
    public function __construct(
            $name,
            array $localColumnNames,
            $referencedTableName,
            array $referencedColumnNames,
            $onDeleteMode,
            $onUpdateMode
    ) {
        InvalidArgumentException::verify(
                count($localColumnNames) === count($referencedColumnNames),
                'Local columns must match the amount of referenced columns: %s != %s',
                count($localColumnNames), count($referencedColumnNames)
        );

        ForeignKeyMode::validate($onDeleteMode);
        ForeignKeyMode::validate($onUpdateMode);

        $this->name                  = $name;
        $this->localColumnNames      = array_values($localColumnNames);
        $this->referencedTableName   = $referencedTableName;
        $this->referencedColumnNames = array_values($referencedColumnNames);
        $this->onDeleteMode          = $onDeleteMode;
        $this->onUpdateMode          = $onUpdateMode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getLocalColumnNames()
    {
        return $this->localColumnNames;
    }

    /**
     * @return string
     */
    public function getReferencedTableName()
    {
        return $this->referencedTableName;
    }

    /**
     * @return string[]
     */
    public function getReferencedColumnNames()
    {
        return $this->referencedColumnNames;
    }

    /**
     * @return string
     */
    public function getOnDeleteMode()
    {
        return $this->onDeleteMode;
    }

    /**
     * @return string
     */
    public function getOnUpdateMode()
    {
        return $this->onUpdateMode;
    }

    /**
     * Gets the foreign key with the *local* columns prefixed
     * and the name prefixed.
     *
     * @param string $prefix
     *
     * @return ForeignKey
     */
    public function withPrefix($prefix)
    {
        $prefixedLocalColumns = [];

        foreach ($this->localColumnNames as $name) {
            $prefixedLocalColumns[] = $prefix . $name;
        }

        return new ForeignKey(
                $prefix . $this->name,
                $prefixedLocalColumns,
                $this->referencedTableName,
                $this->referencedColumnNames,
                $this->onDeleteMode,
                $this->onUpdateMode
        );
    }
}