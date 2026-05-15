<?php

namespace App\Providers;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\Visibility;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('gcs', function ($app, array $config) {
            $storageClient = new StorageClient(array_filter([
                'projectId' => $config['project_id'] ?? null,
                'keyFilePath' => $config['key_file'] ?? null,
            ]));

            $adapter = new GoogleCloudStorageAdapter(
                bucket: $storageClient->bucket($config['bucket']),
                prefix: $config['path_prefix'] ?? '',
                defaultVisibility: $config['visibility'] ?? Visibility::PUBLIC,
            );

            return new FilesystemAdapter(
                new FlysystemFilesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }
}
