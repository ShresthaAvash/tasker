<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Organization\DashboardController;

use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        // If the user is logged in, redirect them to another page (e.g. dashboard)
        return view('home'); // Change 'dashboard' to your actual route
    }
    return view('auth.login'); // If not logged in, show the login page
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');



// ✅ Super Admin routes for managing organizations

Route::middleware(['auth', 'isSuperAdmin'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');    
    Route::get('/organizations', [SuperAdminController::class, 'index'])->name('superadmin.organizations.index');
    Route::get('/organizations/create', [SuperAdminController::class, 'create'])->name('superadmin.organizations.create');
    Route::post('/organizations', [SuperAdminController::class, 'store'])->name('superadmin.organizations.store');
    Route::get('/organizations/{id}', [SuperAdminController::class, 'show'])->name('superadmin.organizations.show');
    Route::get('/organizations/{id}/edit', [SuperAdminController::class, 'edit'])->name('superadmin.organizations.edit');
    Route::put('/organizations/{id}', [SuperAdminController::class, 'update'])->name('superadmin.organizations.update');
    Route::delete('/organizations/{id}', [SuperAdminController::class, 'destroy'])->name('superadmin.organizations.destroy');
});

Route::middleware(['auth', 'isOrganization'])->prefix('organization')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard');
    Route::resource('clients', \App\Http\Controllers\Organization\ClientController::class);

});
require __DIR__.'/auth.php';
