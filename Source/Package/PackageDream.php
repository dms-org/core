<?php

namespace Iddigital\Cms\Core\Package;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PackageDream extends Package
{
    public function define(PackageDefinition $package)
    {
        $package->name('blog');

        // MAYBE:
        // $package->config(SomeStagedFormObject::class);

        // MAYBE:
        // $package->dashboard('dashboard', SomeModule::class);

        $package->modules([
                'categories' => CategoryModule::class,
                'posts'      => PostModule::class,
                'comment'    => CommentModule::class,
        ]);
    }
}