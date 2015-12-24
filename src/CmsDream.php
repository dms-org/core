<?php

namespace Dms\Core;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CmsDream extends Cms
{


    public function define(CmsDefinition $cms)
    {

        // MAYBE:
        // $cms->config(SomeStagedFormObject::class);

        // MAYBE:
        // $cms->dashboard(SomeModule::class);

        $cms->packages([
                'cms-user' => CmsUserPackage::class,
                'user'     => UserPackage::class,
                'store'    => StorePackage::class,
                'blog'     => BlogPackage::class,
        ]);
    }
}