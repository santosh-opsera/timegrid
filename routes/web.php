<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'welcome'])->name('home');
Route::get('/directory', [PublicController::class, 'directory'])->name('directory');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('businesses', BusinessController::class);

    Route::prefix('businesses/{business}')->name('businesses.')->group(function () {
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
        Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
        Route::put('contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

        Route::get('vacancies', [VacancyController::class, 'index'])->name('vacancies.index');
        Route::post('vacancies', [VacancyController::class, 'store'])->name('vacancies.store');
        Route::post('vacancies/bulk', [VacancyController::class, 'bulkStore'])->name('vacancies.bulk');
        Route::delete('vacancies/{vacancy}', [VacancyController::class, 'destroy'])->name('vacancies.destroy');

        Route::get('agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::get('calendar', [AgendaController::class, 'calendar'])->name('agenda.calendar');
        Route::post('appointments/{appointment}/action', [AgendaController::class, 'action'])->name('appointments.action');
    });
});

Route::get('/book/{business}', [BookingController::class, 'show'])->name('booking.show');

Route::middleware('auth')->group(function () {
    Route::post('/book/{business}', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/book/{business}/confirmation/{appointment}', [BookingController::class, 'confirmation'])->name('booking.confirmation');
});

require __DIR__.'/auth.php';
