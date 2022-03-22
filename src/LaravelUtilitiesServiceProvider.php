<?php declare(strict_types=1);

namespace Chiiya\Common;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelUtilitiesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-utilities')
            ->hasConfigFile();
    }
}
