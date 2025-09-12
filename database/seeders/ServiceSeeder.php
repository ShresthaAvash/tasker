<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use App\Models\Task;
use App\Models\AssignedTask;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to avoid duplication
        Service::truncate();
        Task::truncate();
        AssignedTask::truncate();
        DB::table('client_service')->truncate();
        DB::table('assigned_task_staff')->truncate();

        // Get the main organization, its staff, and clients
        $organization = User::where('type', 'O')->first();
        if (!$organization) {
            return;
        }
        
        $staffMembers = User::where('organization_id', $organization->id)->where('type', 'T')->get();
        $clients = User::where('organization_id', $organization->id)->where('type', 'C')->get();
        
        if ($staffMembers->isEmpty() || $clients->isEmpty()) {
            return;
        }

        // --- Create Service Templates ---
        $service1 = Service::create([
            'name' => 'Annual Accounting',
            'description' => 'Complete annual accounting and tax return services.',
            'organization_id' => $organization->id,
            'status' => 'A',
            'is_recurring' => false,
        ]);

        $service1->tasks()->createMany([
            ['name' => 'Collect Bank Statements', 'start' => now()->addDays(1), 'status' => 'not_started'],
            ['name' => 'Request Expense Reports', 'start' => now()->addDays(2), 'status' => 'not_started'],
            ['name' => 'Prepare Tax Computations', 'start' => now()->addDays(10), 'status' => 'not_started'],
            ['name' => 'File with Tax Authority', 'start' => now()->addDays(15), 'status' => 'not_started'],
        ]);
        
        $service2 = Service::create([
            'name' => 'Monthly Payroll',
            'description' => 'Monthly payroll processing for employees.',
            'organization_id' => $organization->id,
            'status' => 'A',
            'is_recurring' => true,
            'recurring_frequency' => 'monthly',
        ]);
        
        $service2->tasks()->createMany([
            ['name' => 'Process Monthly Timesheets', 'start' => now()->addDays(5), 'end' => now()->addYear(), 'status' => 'not_started'],
            ['name' => 'Generate Payslips', 'start' => now()->addDays(6), 'end' => now()->addYear(), 'status' => 'not_started'],
        ]);
        
        // --- Assign Services and Tasks to Clients ---
        $client1 = $clients->get(0);
        $client2 = $clients->get(1);

        // Assign Service 1 to Client 1
        $client1->assignedServices()->sync([$service1->id => ['start_date' => now()]]);
        
        // Create AssignedTask instances for Client 1 based on Service 1's tasks
        foreach ($service1->tasks as $taskTemplate) {
            $assignedTask = AssignedTask::create([
                'client_id' => $client1->id,
                'task_template_id' => $taskTemplate->id,
                'service_id' => $service1->id,
                'name' => $taskTemplate->name,
                'description' => $taskTemplate->description,
                'status' => 'to_do',
                'start' => $taskTemplate->start,
                'end' => $taskTemplate->end,
                'is_recurring' => $service1->is_recurring,
                'recurring_frequency' => $service1->recurring_frequency,
            ]);
            // Assign a random staff member to this task
            $assignedTask->staff()->sync([$staffMembers->random()->id]);
        }
        
        // Assign Service 2 to Client 2
        $client2->assignedServices()->sync([$service2->id => ['start_date' => now()]]);
        
        foreach ($service2->tasks as $taskTemplate) {
            $assignedTask = AssignedTask::create([
                'client_id' => $client2->id,
                'task_template_id' => $taskTemplate->id,
                'service_id' => $service2->id,
                'name' => $taskTemplate->name,
                'description' => $taskTemplate->description,
                'status' => 'to_do',
                'start' => $taskTemplate->start,
                'end' => $taskTemplate->end,
                'is_recurring' => $service2->is_recurring,
                'recurring_frequency' => $service2->recurring_frequency,
            ]);
            // Assign a random staff member to this task
            $assignedTask->staff()->sync([$staffMembers->random()->id]);
        }
    }
}