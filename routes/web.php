<?php

declare(strict_types=1);

use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Guest\BusinessController as GuestBusinessController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Manager\AddressbookController;
use App\Http\Controllers\Manager\BusinessAgendaController;
use App\Http\Controllers\Manager\BusinessController as ManagerBusinessController;
use App\Http\Controllers\Manager\BusinessNotificationsController;
use App\Http\Controllers\Manager\BusinessPreferencesController;
use App\Http\Controllers\Manager\BusinessServiceController;
use App\Http\Controllers\Manager\BusinessVacancyController;
use App\Http\Controllers\Manager\HumanresourceController;
use App\Http\Controllers\Manager\Search;
use App\Http\Controllers\Manager\ServiceTypeController;
use App\Http\Controllers\Root\RootController;
use App\Http\Controllers\User\AgendaController;
use App\Http\Controllers\User\BusinessController as UserBusinessController;
use App\Http\Controllers\User\ContactController;
use App\Http\Controllers\User\ICalController;
use App\Http\Controllers\User\UserPreferencesController;
use App\Http\Controllers\User\WizardController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WhoopsController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show'])
    ->middleware('web');

Route::get('/health', function () {
    require_once base_path('scripts/health-check.php');

    $result = perform_health_check();
    $statusCode = $result['status'] === 'healthy' ? 200 : 503;

    return response()->json($result, $statusCode);
})->name('health');

//////////////////
// ROOT CONTEXT //
//////////////////

Route::prefix('root')
    ->as('root.')
    ->middleware(['auth', 'role:root', 'throttle:60,1'])
    ->group(function (): void {
        Route::controller(RootController::class)->group(function (): void {
            Route::get('dashboard', 'getIndex')->name('dashboard');
            Route::get('sudo/{userId}', 'getSudo')
                ->name('sudo')
                ->where('userId', '\d+');
        });
    });

//////////////////
// REGULAR AUTH //
//////////////////

Route::middleware('guest')->group(function (): void {
    Route::controller(LoginController::class)->group(function (): void {
        Route::get('login', 'showLoginForm')->name('login');
        Route::post('login', 'login')->middleware('throttle:6,1');
    });

    Route::controller(RegisterController::class)->group(function (): void {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register')->middleware('throttle:6,1');
    });

    Route::controller(ForgotPasswordController::class)->group(function (): void {
        Route::get('password/reset', 'showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'sendResetLinkEmail')->name('password.email')->middleware('throttle:6,1');
    });

    Route::controller(ResetPasswordController::class)->group(function (): void {
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
        Route::post('password/reset', 'reset')->name('password.update')->middleware('throttle:6,1');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

//////////
// AJAX //
//////////

Route::controller(BookingController::class)
    ->middleware(['auth', 'throttle:60,1'])
    ->group(function (): void {
        Route::post('booking', 'postAction')->name('api.booking.action');
    });

///////////////////
// GUEST CONTEXT //
///////////////////

Route::controller(WelcomeController::class)->group(function (): void {
    Route::get('/', 'index')->name('welcome');
});

Route::controller(LanguageController::class)->group(function (): void {
    Route::get('lang/{lang}', 'switchLang')->name('lang.switch');
});

Route::controller(OAuthController::class)->group(function (): void {
    Route::get('social/login/redirect/{provider}', 'redirectToProvider')
        ->name('social.login')
        ->where('provider', 'google|facebook|github')
        ->middleware('throttle:10,1');

    Route::get('social/login/{provider}', 'handleProviderCallback')
        ->where('provider', 'google|facebook|github')
        ->middleware('throttle:10,1');
});

Route::controller(WhoopsController::class)->group(function (): void {
    Route::get('whoops', 'display')->name('whoops');
});

Route::middleware('auth')->group(function (): void {
    Route::get('home', [WizardController::class, 'getWizard'])->name('home');
});

//////////////////
// USER CONTEXT //
//////////////////

Route::prefix('user')
    ->middleware(['auth', 'throttle:120,1'])
    ->group(function (): void {
        Route::controller(UserPreferencesController::class)->group(function (): void {
            Route::get('preferences', 'getPreferences')->name('user.preferences');
            Route::post('preferences', 'postPreferences');
        });

        Route::controller(AgendaController::class)->group(function (): void {
            Route::get('agenda', 'getIndex')->name('user.agenda');
        });

        Route::controller(ManagerBusinessController::class)->group(function (): void {
            Route::get('businesses/register/{plan?}', 'create')->name('manager.business.register');
            Route::post('businesses/register', 'store')->name('manager.business.store');
            Route::get('businesses', 'index')->name('manager.business.index');
        });

        Route::controller(UserBusinessController::class)->group(function (): void {
            Route::get('directory', 'getList')->name('user.directory.list');
            Route::get('subscriptions', 'getSubscriptions')->name('user.subscriptions');
        });

        Route::get('dashboard', [WizardController::class, 'getDashboard'])->name('user.dashboard');

        Route::get('profile', function () {
            return \Inertia\Inertia::render('Profile/Edit', [
                'user' => auth()->user(),
            ]);
        })->name('user.profile');

        Route::as('wizard.')->group(function (): void {
            Route::controller(WizardController::class)->group(function (): void {
                Route::get('terms', 'getTerms')->name('terms');
                Route::get('wizard', 'getWelcome')->name('welcome');
                Route::get('pricing', 'getPricing')->name('pricing');
            });
        });
    });

////////////////////////////////////
// SELECTED BUSINESS SLUG CONTEXT //
////////////////////////////////////

Route::prefix('{business}')
    ->middleware(['throttle:120,1'])
    ->group(function (): void {
        Route::get('ical/{token}', [ICalController::class, 'download'])
            ->name('business.ical.download');

        Route::prefix('user')
            ->as('user.')
            ->group(function (): void {
                Route::prefix('agenda')
                    ->as('booking.')
                    ->controller(AgendaController::class)
                    ->group(function (): void {
                        Route::post('store', 'postStore')->name('store');
                        Route::get('book', 'getAvailability')->name('book');
                        Route::get('validate', 'getValidate')->name('validate');
                    });

                Route::prefix('businesses')
                    ->as('businesses.')
                    ->controller(UserBusinessController::class)
                    ->group(function (): void {
                        Route::get('home', 'getHome')->name('home');
                    });

                Route::controller(ContactController::class)->group(function (): void {
                    Route::get('contact', 'index')->name('business.contact.index');
                    Route::get('contact/create', 'create')->name('business.contact.create');
                    Route::post('contact', 'store')->name('business.contact.store');
                    Route::get('contact/{contact}', 'show')->name('business.contact.show');
                    Route::get('contact/{contact}/edit', 'edit')->name('business.contact.edit');
                    Route::put('contact/{contact}', 'update')->name('business.contact.update');
                    Route::delete('contact/{contact}', 'destroy')->name('business.contact.destroy');
                });
            });

        Route::prefix('manage')
            ->middleware('auth')
            ->group(function (): void {
                Route::controller(BusinessPreferencesController::class)->group(function (): void {
                    Route::get('preferences', 'getPreferences')->name('manager.business.preferences');
                    Route::post('preferences', 'postPreferences');
                });

                Route::controller(BusinessAgendaController::class)->group(function (): void {
                    Route::get('agenda', 'getIndex')->name('manager.business.agenda.index');
                    Route::get('calendar', 'getCalendar')->name('manager.business.agenda.calendar');
                    Route::post('agenda/{appointment}/status', 'updateStatus')->name('manager.business.agenda.status');
                });

                Route::controller(ManagerBusinessController::class)->group(function (): void {
                    Route::get('dashboard', 'show')->name('manager.business.show');
                    Route::get('edit', 'edit')->name('manager.business.edit');
                    Route::put('', 'update')->name('manager.business.update');
                    Route::delete('', 'destroy')->name('manager.business.destroy');
                });

                Route::get('notifications', [BusinessNotificationsController::class, 'show'])
                    ->name('manager.business.notifications.show');

                Route::post('search', [Search::class, 'postSearch'])
                    ->name('manager.search');

                Route::prefix('contact')
                    ->controller(AddressbookController::class)
                    ->group(function (): void {
                        Route::get('', 'index')->name('manager.addressbook.index');
                        Route::get('create', 'create')->name('manager.addressbook.create');
                        Route::post('', 'store')->name('manager.addressbook.store');
                        Route::get('{contact}', 'show')->name('manager.addressbook.show');
                        Route::get('{contact}/edit', 'edit')->name('manager.addressbook.edit');
                        Route::put('{contact}', 'update')->name('manager.addressbook.update');
                        Route::delete('{contact}', 'destroy')->name('manager.addressbook.destroy');
                    });

                Route::prefix('humanresources')
                    ->controller(HumanresourceController::class)
                    ->group(function (): void {
                        Route::get('', 'index')->name('manager.business.humanresource.index');
                        Route::get('create', 'create')->name('manager.business.humanresource.create');
                        Route::post('', 'store')->name('manager.business.humanresource.store');
                        Route::get('{humanresource}', 'show')->name('manager.business.humanresource.show');
                        Route::get('{humanresource}/edit', 'edit')->name('manager.business.humanresource.edit');
                        Route::put('{humanresource}', 'update')->name('manager.business.humanresource.update');
                        Route::delete('{humanresource}', 'destroy')->name('manager.business.humanresource.destroy');
                    });

                Route::prefix('service')
                    ->group(function (): void {
                        Route::prefix('type')
                            ->controller(ServiceTypeController::class)
                            ->group(function (): void {
                                Route::get('edit', 'edit')->name('manager.business.servicetype.edit');
                                Route::put('', 'update')->name('manager.business.servicetype.update');
                            });

                        Route::controller(BusinessServiceController::class)->group(function (): void {
                            Route::get('', 'index')->name('manager.business.service.index');
                            Route::get('create', 'create')->name('manager.business.service.create');
                            Route::post('', 'store')->name('manager.business.service.store');
                            Route::get('{service}', 'show')->name('manager.business.service.show');
                            Route::get('{service}/edit', 'edit')->name('manager.business.service.edit');
                            Route::put('{service}', 'update')->name('manager.business.service.update');
                            Route::delete('{service}', 'destroy')->name('manager.business.service.destroy');
                        });
                    });

                Route::prefix('vacancy')
                    ->controller(BusinessVacancyController::class)
                    ->group(function (): void {
                        Route::get('show', 'show')->name('manager.business.vacancy.show');
                        Route::get('create', 'create')->name('manager.business.vacancy.create');
                        Route::post('storeBatch', 'storeBatch')->name('manager.business.vacancy.storeBatch');
                        Route::post('', 'store')->name('manager.business.vacancy.store');
                        Route::post('update', 'update')->name('manager.business.vacancy.update');
                    });
            });
    });

Route::get('{slug}', [GuestBusinessController::class, 'getHome'])
    ->name('guest.business.home')
    ->where('slug', '[^_]+.*');
