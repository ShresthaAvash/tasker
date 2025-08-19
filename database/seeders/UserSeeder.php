<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Plan;
use App\Models\StaffDesignation;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing users and designations to start fresh
        User::truncate();
        StaffDesignation::truncate();

        // 1. Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'S',
        ]);

        // 2. Create the main Organization
        $organization = User::create([
            'name' => 'Innovate Accounting Inc.',
            'email' => 'gtech@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'O',
            'stripe_id' => 'cus_' . Str::random(14),
        ]);
        
        $organization->organization_id = $organization->id;
        $organization->save();
        
        // 3. CREATE A SUBSCRIPTION FOR THE ORGANIZATION
        $monthlyPlan = Plan::where('type', 'monthly')->first();

        if ($monthlyPlan) {
            $subscription = $organization->subscriptions()->create([
                'type' => 'default',
                'stripe_id' => 'sub_' . Str::random(14),
                'stripe_status' => 'active',
                'stripe_price' => $monthlyPlan->stripe_price_id,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
                'current_period_end' => now()->addMonth(), // --- THIS IS THE MODIFICATION ---
            ]);

            $subscription->items()->create([
                'stripe_id' => 'si_' . Str::random(14),
                'stripe_product' => 'prod_dummy_monthly',
                'stripe_price' => $monthlyPlan->stripe_price_id,
                'quantity' => 1,
            ]);
        }
        
        // 4. Create Staff Designations for this Organization
        $seniorAccountant = StaffDesignation::create([
            'name' => 'Senior Accountant',
            'organization_id' => $organization->id,
        ]);
        
        $juniorAssociate = StaffDesignation::create([
            'name' => 'Junior Associate',
            'organization_id' => $organization->id,
        ]);

        // 5. Create Staff Members for this Organization
        User::create([
            'name' => 'Alice Staff',
            'email' => 'staff1@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'T',
            'organization_id' => $organization->id,
            'staff_designation_id' => $seniorAccountant->id,
        ]);

        User::create([
            'name' => 'Bob Worker',
            'email' => 'staff2@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'T',
            'organization_id' => $organization->id,
            'staff_designation_id' => $juniorAssociate->id,
        ]);

        // 6. Create Clients for this Organization
        User::create([
            'name' => 'Global Corp',
            'email' => 'client1@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'C',
            'organization_id' => $organization->id,
        ]);

        User::create([
            'name' => 'Local Biz LLC',
            'email' => 'client2@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'C',
            'organization_id' => $organization->id,
        ]);
    }
}