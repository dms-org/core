<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\User;

/**
 * The typed object mapper base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserMapper extends EntityMapper
{
    private $aliasMapper;
    /**
     * @var PostMapper
     */
    private $postMapper;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->postMapper  = new PostMapper($this);
        $this->aliasMapper = new AliasMapper();
        parent::__construct('users');
    }

    /**
     * @return PostMapper
     */
    public function getPostMapper()
    {
        return $this->postMapper;
    }

    /**
     * @return AliasMapper
     */
    public function getAliasMapper()
    {
        return $this->aliasMapper;
    }


    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(User::class);

        $map->idToPrimaryKey('id');

        $map->property('firstName')->to('first_name')->asVarchar(255);
        $map->property('lastName')->to('last_name')->asVarchar(255);
        $map->property('email')->to('email')->asVarchar(255);
        $map->property('dateOfBirth')->to('dob')->asDate();

        $map->enum('gender')->to('gender')->usingValuesFromConstants();
        $map->enum('status')->to('status')->usingValuesFromConstants();

        $map->embedded('password')
                ->withColumnsPrefixedBy('password_')
                ->using(new PasswordMapper());

        $map->relation('postIds')
                ->using($this->postMapper)
                ->toManyIds()
                ->withParentIdAs('author_id');

        $map->relation('friendIds')
                ->using($this)
                ->toManyIds()
                ->throughJoinTable('user_friends')
                ->withParentIdAs('user_id')
                ->withRelatedIdAs('friend_id');

        $map->relation('alias')
                ->using($this->aliasMapper)
                ->toOne()
                ->identifying()
                ->withParentIdAs('user_id');
    }
}