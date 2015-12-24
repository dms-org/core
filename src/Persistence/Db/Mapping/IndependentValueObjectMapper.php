<?php

namespace Dms\Core\Persistence\Db\Mapping;

/**
 * The simple value object mapper base class.
 *
 * This class is designed for value objects which
 * do not have any relations and hence can be used
 * without knowledge of the parent object mapper.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class IndependentValueObjectMapper extends ValueObjectMapper
{
    public function __construct()
    {
        parent::__construct(new CustomOrm(function () {}), null);

        $this->initializeRelations();
    }
}