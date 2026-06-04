<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Daikazu\FilamentMeta\FilamentMetaServiceProvider;
use Daikazu\LaravelMeta\LaravelMetaServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\Livewire\Partials\DataStoreOverride;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Livewire\Mechanisms\DataStore;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Workbench\App\Models\User;
use Workbench\App\Providers\AdminPanelProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->actingAs(User::create(['name' => 'Tester', 'email' => 'tester@example.com', 'password' => 'secret']));
    }

    protected function getPackageProviders($app): array
    {
        return [
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            NotificationsServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            LaravelMetaServiceProvider::class,
            FilamentMetaServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:'.base64_encode('AckfSECXIvnK5r28GVIWUAxmbBSjTsmF'));

        // Root cause of the "ViewErrorBag::put() ... null given" render error:
        // Livewire keeps every component's error bag in DataStore, a WeakMap that
        // only works if DataStore resolves to ONE shared instance. Livewire binds
        // it as such via app()->instance(), but Filament's SupportServiceProvider
        // re-binds it with a plain (non-shared) bind():
        //   $this->app->bind(DataStore::class, DataStoreOverride::class);
        // In a real HTTP request Filament's panel/Livewire request lifecycle keeps
        // a single resolution alive, so the WeakMap persists. In the Livewire test
        // renderer there is no such lifecycle, so each app(DataStore::class) call
        // returns a fresh DataStoreOverride and the stored error bag is lost ->
        // getErrorBag() returns null -> the TypeError. Re-bind the override as a
        // singleton so its WeakMap survives across resolutions during the test,
        // while keeping Filament's DataStoreOverride behaviour intact.
        $app->singleton(DataStore::class, DataStoreOverride::class);

        $metaBase = __DIR__.'/../vendor/daikazu/laravel-meta/database/migrations';
        (include $metaBase.'/create_model_meta_table.php.stub')->up();
        (include $metaBase.'/create_model_meta_index_table.php.stub')->up();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
    }
}
