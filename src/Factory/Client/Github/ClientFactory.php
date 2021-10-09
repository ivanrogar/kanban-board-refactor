<?php

declare(strict_types=1);

namespace KanbanBoard\Factory\Client\Github;

use Github\Client;
use Github\HttpClient\Builder;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class ClientFactory
{
    public function create(): Client
    {
        $adapter = new Local(
            dirname(__FILE__) . '/../../../../var'
        );

        $fileSystem = new Filesystem($adapter);

        $pool = new FilesystemCachePool($fileSystem);

        $builder = new Builder();

        $builder->addCache($pool);

        return new Client($builder);
    }
}
