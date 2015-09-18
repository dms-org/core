<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\DbRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ProductCategoryRepository extends DbRepository
{
    /**
     * @inheritDoc
     */
    public function __construct(IConnection $connection)
    {
        parent::__construct($connection, new ProductCategoryMapper());
    }
}