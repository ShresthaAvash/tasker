<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Order is important here!
        // 1. Plans must exist first.
        // 2. Users (Org, Staff, Clients) must be created.
        // 3. Services and their relationships can then be created.
        $this->call([
            PlanSeeder::class,
            UserSeeder::class,
            ServiceSeeder::class,
        ]);
    }
}