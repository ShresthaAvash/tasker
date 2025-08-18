<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cashier Model
    |--------------------------------------------------------------------------
    */
    'model' => env('CASHIER_MODEL', App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Subscription Model
    |--------------------------------------------------------------------------
    | This is the crucial line. It tells Cashier to use your custom model,
    | which contains the 'plan' relationship method.
    */
    'subscription_model' => App\Models\Subscription::class,

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('CASHIER_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    */
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en'),

];