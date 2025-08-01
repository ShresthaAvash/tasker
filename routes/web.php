<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



// ✅ Super Admin routes for managing organizations
Route::middleware(['auth', 'isSuperAdmin'])->prefix('superadmin')->group(function () {
    Route::get('/organizations', [SuperAdminController::class, 'index'])->name('superadmin.organizations.index');
    Route::get('/organizations/create', [SuperAdminController::class, 'create'])->name('superadmin.organizations.create');
    Route::post('/organizations', [SuperAdminController::class, 'store'])->name('superadmin.organizations.store');
    Route::get('/organizations/{id}', [SuperAdminController::class, 'show'])->name('superadmin.organizations.show');
    Route::get('/organizations/{id}/edit', [SuperAdminController::class, 'edit'])->name('superadmin.organizations.edit');
    Route::put('/organizations/{id}', [SuperAdminController::class, 'update'])->name('superadmin.organizations.update');
    Route::delete('/organizations/{id}', [SuperAdminController::class, 'destroy'])->name('superadmin.organizations.destroy');
});

require __DIR__.'/auth.php';
