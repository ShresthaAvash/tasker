<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Organization\DashboardController;
use App\Http\Controllers\Organization\ClientController;
use App\Http\Controllers\Organization\StaffDesignationController;
use App\Http\Controllers\Organization\StaffController;
use App\Http\Controllers\Organization\ServiceController;
use App\Http\Controllers\Organization\JobController;
use App\Http\Controllers\Organization\TaskController;
use App\Http\Controllers\Organization\CalendarController;

use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->type === 'S') return redirect()->route('superadmin.dashboard');
        if (Auth::user()->type === 'O') return redirect()->route('organization.dashboard');
        if (Auth::user()->type === 'T') return redirect()->route('staff.dashboard');
        return view('home'); 
    }
    return view('auth.login');
})->name('home');

// Super Admin routes
Route::middleware(['auth', 'isSuperAdmin'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');
    Route::resource('organizations', SuperAdminController::class)->names('superadmin.organizations');
});

// Organization routes
Route::middleware(['auth', 'isOrganization'])->prefix('organization')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard');

    // Calendar Routes
    Route::get('calendar', [CalendarController::class, 'index'])->name('organization.calendar');
    Route::post('calendar/ajax', [CalendarController::class, 'ajax'])->name('organization.calendar.ajax');

    // Client Management
    Route::get('clients/suspended', [ClientController::class, 'suspended'])->name('clients.suspended');
    Route::patch('clients/{client}/status', [ClientController::class, 'toggleStatus'])->name('clients.toggleStatus');
    Route::resource('clients', ClientController::class);

    // Routes for Client Contacts
    Route::post('clients/{client}/contacts', [ClientController::class, 'storeContact'])->name('clients.contacts.store');
    Route::put('client-contacts/{contact}', [ClientController::class, 'updateContact'])->name('clients.contacts.update');
    Route::delete('client-contacts/{contact}', [ClientController::class, 'destroyContact'])->name('clients.contacts.destroy');

    // Routes for Client Notes
    Route::post('clients/{client}/notes', [ClientController::class, 'storeNote'])->name('clients.notes.store');
    Route::put('client-notes/{note}', [ClientController::class, 'updateNote'])->name('clients.notes.update');
    Route::delete('client-notes/{note}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');
    Route::patch('client-notes/{note}/pin', [ClientController::class, 'pinNote'])->name('clients.notes.pin');
    Route::patch('client-notes/{note}/unpin', [ClientController::class, 'unpinNote'])->name('clients.notes.unpin');

    // Staff Designation Management
    Route::resource('staff-designations', StaffDesignationController::class);

    // Staff Member Management
    Route::get('staff/suspended', [StaffController::class, 'suspended'])->name('staff.suspended');
    Route::patch('staff/{staff}/status', [StaffController::class, 'toggleStatus'])->name('staff.toggleStatus');
    Route::resource('staff', StaffController::class);
    
    // Service, Job, and Task Management
    Route::get('services/suspended', [ServiceController::class, 'suspended'])->name('services.suspended');
    Route::patch('services/{service}/status', [ServiceController::class, 'toggleStatus'])->name('services.toggleStatus');
    Route::resource('services', ServiceController::class);

    // Nested routes for Jobs (within a Service) and Tasks (within a Job)
    Route::resource('services.jobs', JobController::class)->shallow()->only(['store', 'update', 'destroy', 'edit']);
    Route::resource('jobs.tasks', TaskController::class)->shallow()->only(['store', 'update', 'destroy']);
    
    // Route for assigning staff to tasks via AJAX
    Route::post('tasks/{task}/assign-staff', [TaskController::class, 'assignStaff'])->name('tasks.assignStaff');

        // ✅ ADDED: Routes for Client Documents
    Route::post('clients/{client}/documents', [ClientController::class, 'storeDocument'])->name('clients.documents.store');
    Route::delete('client-documents/{document}', [ClientController::class, 'destroyDocument'])->name('clients.documents.destroy');
    Route::get('client-documents/{document}/download', [ClientController::class, 'downloadDocument'])->name('clients.documents.download');

        // ✅ ADDED: Routes for Service Assignment
    Route::get('services/get-jobs-for-assignment', [ClientController::class, 'getJobsForServiceAssignment'])->name('clients.services.getJobs');
    Route::post('clients/{client}/assign-services', [ClientController::class, 'assignServices'])->name('clients.services.assign');
});


Route::middleware(['auth', 'isStaff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard');
    Route::get('calendar', [CalendarController::class, 'index'])->name('staff.calendar');

    // Add staff tasks resource
    Route::resource('tasks', TaskController::class)->names('staff.tasks');
});
require __DIR__.'/auth.php';