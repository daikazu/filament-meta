<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentMetaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-meta')
            ->hasConfigFile();
    }
}
