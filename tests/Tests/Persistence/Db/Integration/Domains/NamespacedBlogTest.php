<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains;

use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Tests\Persistence\Db\Mock\MockDatabase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NamespacedBlogTest extends BlogTest
{
    /**
     * @inheritDoc
     */
    protected function loadOrm()
    {
        return parent::loadOrm()->inNamespace('blog_');
    }

    /**
     * @inheritDoc
     */
    protected function getTableAndConstraintNamespace()
    {
        return 'blog_';
    }
    /**
     * @inheritDoc
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);

        $this->userMapper           = $db->getTable('blog_users');
        $this->aliasTable           = $db->getTable('blog_aliases');
        $this->postTable            = $db->getTable('blog_posts');
        $this->commentTable         = $db->getTable('blog_comments');
        $this->userFriendsJoinTable = $db->getTable('blog_user_friends');
    }

}