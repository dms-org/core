<?php declare(strict_types = 1);

namespace Dms\Core\Package;

use Dms\Core\Package\Definition\PackageDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageDream extends Package
{
    public function define(PackageDefinition $package)
    {
        $package->name('blog');

        $package->config(SomeStagedFormObject::class);

        $package->dashboard()
                ->widgets([
                        'categories.most-updated',
                        'posts.recent',
                ]);

        $package->modules([
                'categories' => CategoryModule::class,
                'posts'      => PostModule::class,
                'comment'    => CommentModule::class,
        ]);
    }
}