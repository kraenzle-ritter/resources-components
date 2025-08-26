<?php

namespace KraenzleRitter\ResourcesComponents\Helpers;

use Composer\InstalledVersions;

class UserAgent
{
    public static function get(): array
    {
        $version = InstalledVersions::getPrettyVersion('kraenzle-ritter/resources-components');
        return ['User-Agent' => config(
                    'kraenzle-ritter-resources-components.user_agent',
                    env('RESOURCES_COMPONENTS_USER_AGENT', 'resources-components/'.$version.' (+https://github.com/kraenzle-ritter/resources-components)')
                )];
    }
}
