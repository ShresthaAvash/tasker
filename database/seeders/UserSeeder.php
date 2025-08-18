<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'status' => 'A',
            'type' => 'S', // ✅ Corrected type for Super Admin
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Organization
        $organization = User::create([
            'name' => 'Gtech Vision',
            'email' => 'gtech@gmail.com', // ✅ Corrected to be a valid email
            'password' => Hash::make('password'),
            'status' => 'A', // ✅ Set a default status
            'type' => 'O', // ✅ Corrected type for Organization
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Set the organization_id to its own id
        $organization->organization_id = $organization->id;
        $organization->save();
    }
}