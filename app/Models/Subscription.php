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
        'ends_at' => 'datetime',
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
     * --- THIS IS THE DEFINITIVE FIX ---
     * Get the calculated end date for the current billing period with fallbacks.
     *
     * @return \Carbon\Carbon|null
     */
    public function getCalculatedEndsAtAttribute(): ?Carbon
    {
        // 1. For canceled subscriptions, the `ends_at` date is the source of truth.
        if ($this->canceled() && $this->ends_at) {
            return $this->ends_at;
        }

        // 2. For active subscriptions, the end of the current billing period is the renewal date.
        if ($this->current_period_end) {
            return $this->current_period_end;
        }
        
        // 3. FALLBACK for active subscriptions where current_period_end might be null (e.g., in testing).
        // We calculate it manually based on the creation date and plan type.
        if ($this->active() && !$this->onTrial() && $this->plan) {
            if ($this->plan->type === 'monthly') {
                return $this->created_at->addMonth();
            }
            if ($this->plan->type === 'annually') {
                return $this->created_at->addYear();
            }
        }
        
        // 4. For trials, use the trial end date.
        if ($this->onTrial()) {
            return $this->trial_ends_at;
        }

        return null;
    }
}