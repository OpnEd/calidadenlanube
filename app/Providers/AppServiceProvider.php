<?php

namespace App\Providers;

use App\Models\AnesthesiaSheet;
use App\Models\DispatchItems;
use App\Models\ProductReceptionItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Filament\Facades\Filament;
use App\Models\Team;
use App\Observers\DispatchItemsObserver;
use App\Observers\SaleItemObserver;
use App\Observers\SaleObserver;
use App\Services\CartService;
use App\Services\InvoiceService;
use App\Services\SaleService;
use Illuminate\Support\Facades\Gate;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\TeamNotification;
use App\Observers\AnesthesiaSheetObserver;
use App\Observers\ProductReceptionItemObserver;
use App\Repositories\CourseRepository;
use App\Repositories\Interfaces\CourseInterface;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use App\Notifications\Notification;
use Filament\Notifications\Notification as BaseNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Repositories\RoleRepository::class, function ($app) {
            return new \App\Repositories\RoleRepository();
        });

        $this->app->singleton(\App\Services\RoleService::class, function ($app) {
            return new \App\Services\RoleService($app->make(\App\Repositories\RoleRepository::class));
        });
        // Registramos CartService como singleton para
        // que siempre se utilice la misma instancia durante la petición.
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });

        // Registramos InvoiceService
        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService();
        });

        // Registramos SaleService y le inyectamos InvoiceService mediante $app->make()
        $this->app->singleton(SaleService::class, function ($app) {
            return new SaleService($app->make(InvoiceService::class));
        });

        $this->app->bind(DatabaseNotification::class, TeamNotification::class);

        $this->app->bind(CourseInterface::class, CourseRepository::class);

        $this->app->bind(BaseNotification::class, Notification::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            if (Filament::getCurrentPanel()?->getId() === 'tenantManager') {
                return $user->roles()
                    ->where('name', 'Super Admin')
                    ->whereNull('model_has_roles.team_id')
                    ->exists() ? true : null;
            }
        });

        // Suponiendo que hay un team seleccionado (ej. en la sesión)
        /* $teamId = session('team_id', 1); // Asegúrate de que existe

        app(PermissionRegistrar::class)->setPermissionsTeamId($teamId); */

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn(): string => Blade::render('@livewire(\'footer-text-component\')'),
        );
        // Observers
        DispatchItems::observe(DispatchItemsObserver::class);
        Sale::observe(SaleObserver::class);
        SaleItem::observe(SaleItemObserver::class);
        AnesthesiaSheet::observe(AnesthesiaSheetObserver::class);
        ProductReceptionItem::observe(ProductReceptionItemObserver::class);

        /* Notification::configureUsing(function (Notification $notification): void {
            $notification->view('filament.notifications.notification');
        }); */

        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::Center);
    }
}
