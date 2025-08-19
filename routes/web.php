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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController; // <-- Ensure this is here
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
})->middleware(['auth', 'checkUserStatus'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/activity-log', [ProfileController::class, 'showActivityLog'])->name('profile.activity_log');
    // New Subscription Routes
    Route::get('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/store', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    
    // --- THIS IS THE NEW UNIFIED REPORT ROUTE ---
    Route::get('/generate-report', ReportController::class)->name('generate.report');
});

// Super Admin routes
Route::middleware(['auth', 'isSuperAdmin','checkUserStatus'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');

    // --- MODIFICATION START ---
    // The 'subscription-requests' routes have been removed.
    // The 'active-subscriptions' route is replaced by the new 'subscribed' route.
    Route::get('/subscribed-organizations', [SuperAdminController::class, 'subscribedOrganizations'])->name('superadmin.subscriptions.subscribed');
    // --- MODIFICATION END ---
    
    Route::resource('organizations', SuperAdminController::class)->names('superadmin.organizations');
    Route::resource('plans', SuperAdminPlanController::class)->names('superadmin.plans');
    Route::patch('/subscriptions/{user}/cancel', [SuperAdminController::class, 'cancelSubscription'])->name('superadmin.subscriptions.cancel');
    Route::patch('/subscriptions/{user}/resume', [SuperAdminController::class, 'resumeSubscription'])->name('superadmin.subscriptions.resume');
});

// Organization routes
Route::middleware(['auth', 'isOrganization', 'checkUserStatus'])->prefix('organization')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard');
    
    Route::get('subscription', [\App\Http\Controllers\Organization\SubscriptionController::class, 'index'])->name('organization.subscription.index');
    Route::post('subscription', [\App\Http\Controllers\Organization\SubscriptionController::class, 'store'])->name('organization.subscription.store');
    
    // Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('organization.calendar');
    Route::get('calendar/events', [CalendarController::class, 'fetchEvents'])->name('organization.calendar.events');
    Route::post('calendar/ajax', [CalendarController::class, 'ajax'])->name('organization.calendar.ajax');
    
    // Client Management
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
    
    // Staff Management
    Route::resource('staff-designations', StaffDesignationController::class);
    Route::get('staff/suspended', [StaffController::class, 'suspended'])->name('staff.suspended');
    Route::patch('staff/{staff}/status', [StaffController::class, 'toggleStatus'])->name('staff.toggleStatus');
    Route::resource('staff', StaffController::class);
    
    Route::post('clients/{client}/store-service', [ClientController::class, 'storeClientSpecificService'])->name('clients.services.storeForClient');
    
    // Service, Job, and Task Management
    Route::patch('services/{service}/status', [ServiceController::class, 'toggleStatus'])->name('services.toggleStatus');
    Route::resource('services', ServiceController::class);

    // Nested routes for Jobs (within a Service) and Tasks (within a Job)
    Route::resource('services.jobs', JobController::class)->shallow()->only(['store', 'update', 'destroy', 'edit']);
    Route::resource('jobs.tasks', TaskController::class)->shallow()->only(['store', 'update', 'destroy']);
    Route::post('tasks/{task}/assign-staff', [TaskController::class, 'assignStaff'])->name('tasks.assignStaff');
    Route::post('tasks/{task}/stop', [TaskController::class, 'stopTask'])->name('tasks.stop');
    Route::post('jobs/{job}/assign-tasks', [JobController::class, 'assignTasks'])->name('jobs.assignTasks');
});

// Staff routes
Route::middleware(['auth', 'isStaff', 'checkUserStatus'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('staff.dashboard');
    Route::get('calendar', [CalendarController::class, 'index'])->name('staff.calendar');
    Route::get('calendar/events', [CalendarController::class, 'fetchEvents'])->name('staff.calendar.events');
    Route::post('calendar/ajax', [CalendarController::class, 'ajax'])->name('staff.calendar.ajax');
    Route::get('tasks', [StaffTaskController::class, 'index'])->name('staff.tasks.index');

    Route::patch('tasks/{task}/status', [StaffTaskController::class, 'updateStatus'])->name('staff.tasks.updateStatus')->where('task', '.*');

    // --- NEW ROUTES START ---
    Route::post('tasks/{task}/start-timer', [StaffTaskController::class, 'startTimer'])->name('staff.tasks.startTimer')->where('task', '.*');
    Route::post('tasks/{task}/stop-timer', [StaffTaskController::class, 'stopTimer'])->name('staff.tasks.stopTimer')->where('task', '.*');
    // --- NEW ROUTES END ---

    Route::post('tasks/{task}/add-manual-time', [StaffTaskController::class, 'addManualTime'])->name('staff.tasks.addManualTime')->where('task', '.*');
});

require __DIR__.'/auth.php';