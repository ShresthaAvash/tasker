<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set the Stripe API key from your .env file
        Stripe::setApiKey(config('services.stripe.secret'));

        Plan::truncate();

        // --- Create Monthly Plan ---
        try {
            // 1. Create a Product in Stripe
            $monthlyProduct = Product::create([
                'name' => 'Monthly Subscription',
            ]);

            // 2. Create a Price for that Product in Stripe
            $monthlyPrice = Price::create([
                'product' => $monthlyProduct->id,
                'unit_amount' => 5000, // Price in cents ($50.00)
                'currency' => config('cashier.currency'),
                'recurring' => ['interval' => 'month'],
            ]);

            // 3. Create the Plan in your database with the REAL Stripe Price ID
            Plan::create([
                'name' => 'Monthly Subscription',
                'description' => 'Standard monthly access to all features.',
                'price' => 50.00,
                'type' => 'monthly',
                'stripe_price_id' => $monthlyPrice->id, // Use the real ID
            ]);
        } catch (\Exception $e) {
            $this->command->error('Error creating monthly plan in Stripe: ' . $e->getMessage());
        }


        // --- Create Yearly Plan ---
        try {
            // 1. Create a Product in Stripe
            $yearlyProduct = Product::create([
                'name' => 'Yearly Subscription',
            ]);
    
            // 2. Create a Price for that Product in Stripe
            $yearlyPrice = Price::create([
                'product' => $yearlyProduct->id,
                'unit_amount' => 50000, // Price in cents ($500.00)
                'currency' => config('cashier.currency'),
                'recurring' => ['interval' => 'year'],
            ]);
    
            // 3. Create the Plan in your database with the REAL Stripe Price ID
            Plan::create([
                'name' => 'Yearly Subscription',
                'description' => 'Standard yearly access to all features with a discount.',
                'price' => 500.00,
                'type' => 'annually',
                'stripe_price_id' => $yearlyPrice->id, // Use the real ID
            ]);
        } catch (\Exception $e) {
            $this->command->error('Error creating yearly plan in Stripe: ' . $e->getMessage());
        }
    }
}