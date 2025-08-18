<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'current_period_end' => 'datetime',
        'ends_at' => 'datetime', // <-- THIS IS THE FIX
    ];

    /**
     * The relationship to the plan model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'stripe_price', 'stripe_price_id');
    }

    /**
     * Get the calculated end date for the current billing period.
     *
     * @return \Carbon\Carbon|null
     */
    public function getCalculatedEndsAtAttribute(): ?Carbon
    {
        // If the subscription is canceled, the official 'ends_at' is the most accurate.
        if ($this->canceled() && $this->ends_at) {
            return $this->ends_at;
        }

        // For active subscriptions, the 'current_period_end' is the renewal date.
        if ($this->current_period_end) {
            return $this->current_period_end;
        }
        
        // Fallback for older records
        if ($this->plan) {
            $startDate = $this->created_at;

            if ($this->plan->type === 'monthly') {
                return $startDate->addMonth();
            }

            if ($this->plan->type === 'annually') {
                return $startDate->addYear();
            }
        }

        return null;
    }
}