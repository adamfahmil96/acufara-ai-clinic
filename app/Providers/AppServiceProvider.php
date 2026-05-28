<?php

namespace App\Providers;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\Visibility;
use Opcodes\LogViewer\Facades\LogViewer;

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
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Log Viewer: hanya super_admin yang bisa mengakses
        LogViewer::auth(function ($request) {
            return $request->user()?->hasRole('super_admin') ?? false;
        });

        Storage::extend('gcs', function ($app, array $config) {
            $storageClient = new StorageClient(array_filter([
                'projectId' => $config['project_id'] ?? null,
                'keyFilePath' => $config['key_file'] ?? null,
            ]));

            $visibilityHandler = isset($config['visibility_handler']) 
                ? new $config['visibility_handler']() 
                : null;

            $adapter = new GoogleCloudStorageAdapter(
                bucket: $storageClient->bucket($config['bucket']),
                prefix: $config['path_prefix'] ?? '',
                visibilityHandler: $visibilityHandler,
                defaultVisibility: $config['visibility'] ?? Visibility::PUBLIC,
            );

            return new class(
                new FlysystemFilesystem($adapter, $config),
                $adapter,
                $config
            ) extends FilesystemAdapter {
                public function url($path)
                {
                    $bucket = $this->config['bucket'] ?? '';
                    $prefix = $this->config['path_prefix'] ?? '';
                    
                    $fullPath = ltrim(($prefix ? rtrim($prefix, '/') . '/' : '') . ltrim($path, '/'), '/');
                    
                    if (isset($this->config['url'])) {
                        return rtrim($this->config['url'], '/') . '/' . $fullPath;
                    }
                    
                    return 'https://storage.googleapis.com/' . $bucket . '/' . $fullPath;
                }
            };
        });
    }
}
