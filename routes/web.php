<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
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
use App\Http\Controllers\SubscriptionPendingController;
use App\Http\Controllers\Staff\TaskController as StaffTaskController;
use App\Http\Controllers\SubscriptionController; // Add this
use App\Http\Controllers\SuperAdmin\PlanController as SuperAdminPlanController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
// Public Routes
Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/pricing', [LandingPageController::class, 'pricing'])->name('pricing');

// ADD THIS NEW ROUTE FOR THE PENDING PAGE
Route::get('/subscription/pending', [SubscriptionPendingController::class, 'index'])->name('subscription.pending');
// ADD THIS NEW ROUTE FOR THE EXPIRED PAGE
Route::get('/subscription/expired', [SubscriptionPendingController::class, 'expired'])->name('subscription.expired');


Route::get('/dashboard', function () {
    if (Auth::check()) {
        $userType = Auth::user()->type;
        if ($userType === 'S') return redirect()->route('superadmin.dashboard');
        if ($userType === 'O') return redirect()->route('organization.dashboard');
        if ($userType === 'T') return redirect()->route('staff.dashboard');
    }
    return redirect()->route('login');
})->middleware(['auth', 'checkUserStatus'])->name('dashboard'); // <-- ADD 'checkUserStatus' MIDDLEWARE

// This block makes the profile routes available to the whole app.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/activity-log', [ProfileController::class, 'showActivityLog'])->name('profile.activity_log');
    // New Subscription Routes
    Route::get('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/store', [SubscriptionController::class, 'store'])->name('subscription.store');
});

// Route::get('/', function () {
//     if (Auth::check()) {
//         if (Auth::user()->type === 'S') return redirect()->route('superadmin.dashboard');
//         if (Auth::user()->type === 'O') return redirect()->route('organization.dashboard');
//         if (Auth::user()->type === 'T') return redirect()->route('staff.dashboard');
//         return view('home');
//     }
//     return view('auth.login');
// })->name('home');

// Super Admin routes
Route::middleware(['auth', 'isSuperAdmin','checkUserStatus'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('superadmin.dashboard');

    Route::get('/subscription-requests', [SuperAdminController::class, 'subscriptionRequests'])->name('superadmin.subscriptions.requests');
    Route::patch('/subscription-requests/{user}/approve', [SuperAdminController::class, 'approveSubscription'])->name('superadmin.subscriptions.approve');

    Route::resource('organizations', SuperAdminController::class)->names('superadmin.organizations');

    // --- THIS IS THE CORRECTED ROUTE RESOURCE ---
    // It should be 'plans' and point to your renamed 'SuperAdminPlanController'
    Route::resource('plans', SuperAdminPlanController::class)->names('superadmin.plans');

    Route::get('/active-subscriptions', [SuperAdminController::class, 'activeSubscriptions'])->name('superadmin.subscriptions.active');
    Route::patch('/subscriptions/{user}/cancel', [SuperAdminController::class, 'cancelSubscription'])->name('superadmin.subscriptions.cancel');
    Route::patch('/subscriptions/{user}/resume', [SuperAdminController::class, 'resumeSubscription'])->name('superadmin.subscriptions.resume');
});

// Organization routes
Route::middleware(['auth', 'isOrganization', 'checkUserStatus'])->prefix('organization')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard');
    Route::get('/staff/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard');
    
    // ... (All your other organization routes remain the same) ...
    Route::get('calendar', [CalendarController::class, 'index'])->name('organization.calendar');
    Route::get('calendar/events', [CalendarController::class, 'fetchEvents'])->name('organization.calendar.events');
    Route::post('calendar/ajax', [CalendarController::class, 'ajax'])->name('organization.calendar.ajax');
    Route::resource('clients', ClientController::class);
    Route::get('clients/suspended', [ClientController::class, 'suspended'])->name('clients.suspended');
    Route::patch('clients/{client}/status', [ClientController::class, 'toggleStatus'])->name('clients.toggleStatus');
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/contacts', [ClientController::class, 'storeContact'])->name('clients.contacts.store');
    Route::put('client-contacts/{contact}', [ClientController::class, 'updateContact'])->name('clients.contacts.update');
    Route::delete('client-contacts/{contact}', [ClientController::class, 'destroyContact'])->name('clients.contacts.destroy');
    Route::post('clients/{client}/notes', [ClientController::class, 'storeNote'])->name('clients.notes.store');
    Route::put('client-notes/{note}', [ClientController::class, 'updateNote'])->name('clients.notes.update');
    Route::delete('client-notes/{note}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');
    Route::patch('client-notes/{note}/pin', [ClientController::class, 'pinNote'])->name('clients.notes.pin');
    Route::patch('client-notes/{note}/unpin', [ClientController::class, 'unpinNote'])->name('clients.notes.unpin');
    Route::post('clients/{client}/documents', [ClientController::class, 'storeDocument'])->name('clients.documents.store');
    Route::delete('client-documents/{document}', [ClientController::class, 'destroyDocument'])->name('clients.documents.destroy');
    Route::get('client-documents/{document}/download', [ClientController::class, 'downloadDocument'])->name('clients.documents.download');
    Route::get('services/get-jobs-for-assignment', [ClientController::class, 'getJobsForServiceAssignment'])->name('clients.services.getJobs');
    Route::post('clients/{client}/assign-services', [ClientController::class, 'assignServices'])->name('clients.services.assign');
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
    Route::post('tasks/{task}/assign-staff', [TaskController::class, 'assignStaff'])->name('tasks.assignStaff');
});

Route::middleware(['auth', 'isStaff', 'checkUserStatus'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard');
    Route::get('calendar', [CalendarController::class, 'index'])->name('staff.calendar');
    Route::get('calendar/events', [CalendarController::class, 'fetchEvents'])->name('staff.calendar.events');
    Route::post('calendar/ajax', [CalendarController::class, 'ajax'])->name('staff.calendar.ajax');
    Route::get('tasks', [StaffTaskController::class, 'index'])->name('staff.tasks.index');

    // The where('task', '.*') constraint tells Laravel to accept any string for the {task} parameter,
    // which bypasses the automatic model binding and prevents the 404 error.
    Route::patch('tasks/{task}/status', [StaffTaskController::class, 'updateStatus'])->name('staff.tasks.updateStatus')->where('task', '.*');
    Route::patch('tasks/{task}/timer/start', [StaffTaskController::class, 'startTimer'])->name('staff.tasks.timer.start')->where('task', '.*');
    Route::patch('tasks/{task}/timer/stop', [StaffTaskController::class, 'stopTimer'])->name('staff.tasks.timer.stop')->where('task', '.*');
    Route::post('tasks/{task}/timer/manual', [StaffTaskController::class, 'addManualTime'])->name('staff.tasks.timer.manual')->where('task', '.*');
    Route::post('tasks/{task}/stop', [TaskController::class, 'stopTask'])->name('tasks.stop');
    Route::post('jobs/{job}/assign-tasks', [JobController::class, 'assignTasks'])->name('jobs.assignTasks');
});

require __DIR__.'/auth.php';