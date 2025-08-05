<?php

use App\Http\Middleware\AllowRegistration;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckStatus;
use App\Http\Middleware\Demo;
use App\Http\Middleware\KycMiddleware;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\Module;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfAgent;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfMerchant;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RedirectIfNotAgent;
use App\Http\Middleware\RedirectIfNotMerchant;
use App\Http\Middleware\RegistrationStep;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Laramin\Utility\VugiChugi;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::namespace('App\Http\Controllers')->middleware([VugiChugi::mdNm()])->group(function () {
                Route::prefix('api')
                    ->middleware(['api', 'maintenance'])
                    ->group(base_path('routes/api/api.php'));

                Route::prefix('api/agent')
                    ->namespace('Api')
                    ->middleware(['api', 'maintenance'])
                    ->group(base_path('routes/api/agent.php'));

                Route::prefix('api/merchant')
                    ->namespace('Api')
                    ->middleware(['api', 'maintenance'])
                    ->group(base_path('routes/api/merchant.php'));

                Route::middleware(['web'])
                    ->namespace('Admin')
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));

                Route::middleware(['web', 'maintenance'])
                    ->prefix('agent')
                    ->group(base_path('routes/agent.php'));

                Route::middleware(['web', 'maintenance'])
                    ->prefix('merchant')
                    ->group(base_path('routes/merchant.php'));

                Route::middleware(['web', 'maintenance'])
                    ->namespace('Gateway')
                    ->prefix('ipn')
                    ->name('ipn.')
                    ->group(base_path('routes/ipn.php'));

                Route::middleware(['web', 'maintenance'])->prefix('user')->group(base_path('routes/user.php'));
                Route::middleware(['web', 'maintenance'])->group(base_path('routes/web.php'));

            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LanguageMiddleware::class,
            \App\Http\Middleware\ActiveTemplateMiddleware::class,
            \App\Http\Middleware\Logout::class,
        ]);

        $middleware->alias([
            'auth.basic'            => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers'         => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can'                   => \Illuminate\Auth\Middleware\Authorize::class,
            'auth'                  => Authenticate::class,
            'guest'                 => RedirectIfAuthenticated::class,
            'password.confirm'      => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed'                => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle'              => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified'              => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            'admin'                 => RedirectIfNotAdmin::class,
            'admin.guest'           => RedirectIfAdmin::class,

            'agent'                 => RedirectIfNotAgent::class,
            'agent.guest'           => RedirectIfAgent::class,

            'merchant'              => RedirectIfNotMerchant::class,
            'merchant.guest'        => RedirectIfMerchant::class,

            'registration.status'   => AllowRegistration::class,
            'check.status'          => CheckStatus::class,
            'demo'                  => Demo::class,
            'kyc'                   => KycMiddleware::class,
            'registration.complete' => RegistrationStep::class,
            'maintenance'           => MaintenanceMode::class,
            'module'                => Module::class,

            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);

        $middleware->validateCsrfTokens(
            except: ['user/deposit', 'ipn*', 'test/ipn','payment/initiate', 'sandbox/payment/initiate']
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function () {
            if (request()->is('api/*')) {
                return true;
            }
        });
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 401) {
                if (request()->is('api/*')) {
                    $notify[] = 'Unauthorized request';
                    return response()->json([
                        'remark'  => 'unauthenticated',
                        'status'  => 'error',
                        'message' => ['error' => $notify],
                    ]);
                }
            }

            return $response;
        });
    })->create();
