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
use App\Http\Controllers\Organization\TaskController;
use App\Http\Controllers\Organization\CalendarController as OrganizationCalendarController;
use App\Http\Controllers\Staff\CalendarController as StaffCalendarController;
use App\Http\Controllers\SubscriptionPendingController;
use App\Http\Controllers\Staff\TaskController as StaffTaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SuperAdmin\PlanController as SuperAdminPlanController;
use App\Http\Controllers\Organization\ReportController as OrganizationReportController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DocumentController as ClientDocumentController;
use App\Http\Controllers\Client\ReportController as ClientReportController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\SuperAdmin\ContactMessageController as SuperAdminContactMessageController;
use App\Http\Controllers\Organization\TaskWorkingNoteController;
use App\Http\Controllers\TaskCommentController;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
// Public Routes
Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/pricing', [LandingPageController::class, 'pricing'])->name('pricing');
Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact.store');

Route::get('/subscription/pending', [SubscriptionPendingController::class, 'index'])->name('subscription.pending');
Route::get('/subscription/expired', [SubscriptionPendingController::class, 'expired'])->name('subscription.expired');

Route::post('stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::get('/dashboard', function () {
    if (Auth::check()) {
        $userType = Auth::user()->type;
        if ($userType === 'S') return redirect()->route('superadmin.dashboard');
        if ($userType === 'O') return redirect()->route('organization.dashboard');
        if ($userType === 'T') return redirect()->route('staff.dashboard');
        if ($userType === 'C') return redirect()->route('client.dashboard');
    }
    return redirect()->route('login');
})->middleware(['auth', 'checkUserStatus'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/activity-log', [ProfileController::class, 'showActivityLog'])->name('profile.activity_log');
    Route::get('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/store', [SubscriptionController::class, 'store'])->name('subscription.store');
    
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.markAsUnread');

    // Task Comments Routes (for all authorized users)
    Route::get('/tasks/{task}/comments', [TaskCommentController::class, 'index'])->name('tasks.comments.index');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::put('/comments/{comment}', [TaskCommentController::class, 'update'])->name('tasks.comments.update');
    Route::delete('/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
});

// Super Admin routes
Route::middleware(['auth', 'isSuperAdmin','checkUserStatus'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::get('/earnings', [SuperAdminController::class, 'earnings'])->name('superadmin.earnings');
    Route::get('/subscribed-organizations', [SuperAdminController::class, 'subscribedOrganizations'])->name('superadmin.subscriptions.subscribed');
    Route::resource('organizations', SuperAdminController::class)->names('superadmin.organizations');
    Route::get('/organizations/{user}/subscription-history', [SuperAdminController::class, 'subscriptionHistory'])->name('superadmin.subscriptions.history');
    Route::resource('plans', SuperAdminPlanController::class)->names('superadmin.plans');
    Route::patch('/subscriptions/{user}/cancel', [SuperAdminController::class, 'cancelSubscription'])->name('superadmin.subscriptions.cancel');
    Route::patch('/subscriptions/{user}/resume', [SuperAdminController::class, 'resumeSubscription'])->name('superadmin.subscriptions.resume');
    
    Route::get('/messages', [SuperAdminContactMessageController::class, 'index'])->name('superadmin.messages.index');
    Route::get('/messages/{message}', [SuperAdminContactMessageController::class, 'show'])->name('superadmin.messages.show');
});

// Organization routes
Route::middleware(['auth', 'isOrganization', 'checkUserStatus'])->prefix('organization')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('organization.dashboard');
    Route::get('reports/time', [OrganizationReportController::class, 'timeReport'])->name('organization.reports.time');
    Route::get('reports/staff', [OrganizationReportController::class, 'staffReport'])->name('organization.reports.staff');
    Route::get('reports/staff/{staff}', [OrganizationReportController::class, 'individualStaffReport'])->name('organization.reports.individual_staff');
    
    // --- NEW ROUTE FOR INDIVIDUAL CLIENT REPORT ---
    Route::get('reports/client/{client}', [OrganizationReportController::class, 'individualClientReport'])->name('organization.reports.individual_client');

    Route::get('subscription', [\App\Http\Controllers\Organization\SubscriptionController::class, 'index'])->name('organization.subscription.index');
    Route::get('subscription/change', [\App\Http\Controllers\Organization\SubscriptionController::class, 'showChangePlanForm'])->name('organization.subscription.change');
    Route::post('subscription/change', [\App\Http\Controllers\Organization\SubscriptionController::class, 'processChangePlan'])->name('organization.subscription.change.process');
    
    // Calendar (Organization Owner View)
    Route::get('calendar', [OrganizationCalendarController::class, 'index'])->name('organization.calendar');
    Route::get('calendar/events', [OrganizationCalendarController::class, 'fetchEvents'])->name('organization.calendar.events');
    Route::post('calendar/ajax', [OrganizationCalendarController::class, 'ajax'])->name('organization.calendar.ajax');
    
    // Client Management
    Route::post('clients/send-message', [ClientController::class, 'sendMessage'])->name('clients.sendMessage');
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
    Route::get('services/get-tasks-for-assignment', [ClientController::class, 'getTasksForServiceAssignment'])->name('clients.services.getTasks');
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

    Route::resource('services.tasks', TaskController::class)->shallow()->only(['store', 'update', 'destroy']);
    Route::post('tasks/{task}/assign-staff', [TaskController::class, 'assignStaff'])->name('tasks.assignStaff');
    Route::post('tasks/{task}/stop', [TaskController::class, 'stopTask'])->name('tasks.stop');

    // Task Working Notes Routes (Internal Only)
    Route::get('/tasks/{task}/working-notes', [TaskWorkingNoteController::class, 'index'])->name('tasks.working-notes.index');
    Route::post('/tasks/{task}/working-notes', [TaskWorkingNoteController::class, 'store'])->name('tasks.working-notes.store');
    Route::put('/working-notes/{note}', [TaskWorkingNoteController::class, 'update'])->name('tasks.working-notes.update');
    Route::delete('/working-notes/{note}', [TaskWorkingNoteController::class, 'destroy'])->name('tasks.working-notes.destroy');
});

// Staff routes
Route::middleware(['auth', 'isStaff', 'checkUserStatus'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');
    
    // NEW STAFF CALENDAR ROUTES
    Route::get('calendar', [StaffCalendarController::class, 'index'])->name('calendar');
    Route::get('calendar/events', [StaffCalendarController::class, 'fetchEvents'])->name('calendar.events');
    Route::post('calendar/ajax', [StaffCalendarController::class, 'ajax'])->name('calendar.ajax');

    Route::get('tasks', [StaffTaskController::class, 'index'])->name('tasks.index');
    Route::patch('tasks/{task}/status', [StaffTaskController::class, 'updateStatus'])->name('tasks.updateStatus')->where('task', '.*');
    Route::post('tasks/{task}/start-timer', [StaffTaskController::class, 'startTimer'])->name('tasks.startTimer')->where('task', '.*');
    Route::post('tasks/{task}/stop-timer', [StaffTaskController::class, 'stopTimer'])->name('tasks.stopTimer')->where('task', '.*');
    Route::post('tasks/{task}/add-manual-time', [StaffTaskController::class, 'addManualTime'])->name('tasks.addManualTime')->where('task', '.*');

    Route::get('/documents', [\App\Http\Controllers\Staff\DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/{client}', [\App\Http\Controllers\Staff\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\Staff\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [\App\Http\Controllers\Staff\DocumentController::class, 'destroy'])->name('documents.destroy');
});

// Client Portal Routes
Route::middleware(['auth', 'isClient', 'checkUserStatus'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/documents', [ClientDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [ClientDocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [ClientDocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [ClientDocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/reports', [ClientReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';