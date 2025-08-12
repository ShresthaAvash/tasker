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
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->type === 'S') {
            return redirect()->route('superadmin.dashboard');
        }

        // --- THIS IS THE FIX ---
        // If it's an Organization owner, send to the main dashboard
        if ($user->type === 'O') { 
            return redirect()->route('organization.dashboard');
        }
        // If it's a Staff member, send to the new staff dashboard
        if (in_array($user->type, ['A', 'T'])) {
            return redirect()->route('staff.dashboard');
        }
        // --- END OF FIX ---
        
        return view('home'); 
    }
    return view('auth.login');
});

// Super Admin routes
Route::middleware(['auth', 'isSuperAdmin'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');
    Route::resource('organizations', SuperAdminController::class)->names('superadmin.organizations');
});

// Organization routes
Route::middleware(['auth', 'isOrganization'])->prefix('organization')->group(function () {
    // Main Dashboard for Org Owners
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard');

    // --- THIS IS THE NEW ROUTE for Staff ---
    Route::get('/staff/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard');

    // Calendar Routes (shared by both)
    Route::get('calendar', [CalendarController::class, 'index'])->name('organization.calendar');
    Route::post('calendar/ajax', [CalendarController::class, 'ajax'])->name('organization.calendar.ajax');

    // Client Management
    Route::get('clients/suspended', [ClientController::class, 'suspended'])->name('clients.suspended');
    Route::patch('clients/{client}/status', [ClientController::class, 'toggleStatus'])->name('clients.toggleStatus');
    Route::resource('clients', ClientController::class);

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
    
    // Task-specific action routes
    Route::post('tasks/{task}/assign-staff', [TaskController::class, 'assignStaff'])->name('tasks.assignStaff');
    Route::post('tasks/{task}/stop', [TaskController::class, 'stopTask'])->name('tasks.stop');

    // Job-specific action routes
    Route::post('jobs/{job}/assign-tasks', [JobController::class, 'assignTasks'])->name('jobs.assignTasks');
});

require __DIR__ . '/auth.php';