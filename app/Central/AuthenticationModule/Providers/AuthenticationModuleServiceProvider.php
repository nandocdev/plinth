declare(strict_types=1);

namespace App\Central\AuthenticationModule\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthenticationModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Fortify provider if not done in central
        // $this->app->register(FortifyServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
    }

    /**
     * Load the module's routes.
     */
    protected function loadRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('web')
            ->namespace('App\Central\AuthenticationModule\Http\Controllers')
            ->group(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Load the module's views.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'auth');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views/pages', 'pages');
    }

    /**
     * [RIESGOS]
     * - Colisión de nombres si se usa 'auth' como namespace de vistas y Laravel ya tiene algo (aunque 'auth' suele ser una carpeta).
     * - Las rutas de Fortify se cargan antes que este provider si no se controla el orden en bootstrap/app.php.
     */
}
