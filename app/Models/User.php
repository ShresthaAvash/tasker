<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'phone',
        'address',
        'photo',
        'status',
        'organization_id',
        'staff_designation_id',
        // 'subscription_id', // <-- REMOVED THIS LINE
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- THIS IS THE FIX ---
    /**
     * Get the subscriptions for the user.
     *
     * This overrides the default relationship in the Billable trait
     * to ensure it uses our custom Subscription model, which has the 'plan' relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function adminlte_image()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        // Fallback to a default icon or Gravatar
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?d=mp';
    }

    /**
     * Get the URL to the user's profile edit page.
     *
     * @return string
     */
    public function adminlte_profile_url()
    {
        // Return the route name, not the full URL
        return 'profile.edit';
    }
    // --- END OF THE FIX ---


    /**
     * Get the designation for the staff member.
     */
    public function designation()
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    /**
     * Get the contacts for the client user.
     */
    public function contacts()
    {
        return $this->hasMany(ClientContact::class, 'client_id');
    }

    /**
     * Get all notes for the client, ordered by latest.
     */
    public function notes()
    {
        return $this->hasMany(ClientNote::class, 'client_id')->latest();
    }

    /**
     * Get the single pinned note for the client.
     */
    public function pinnedNote()
    {
        return $this->hasOne(ClientNote::class, 'client_id')->whereNotNull('pinned_at')->latest('pinned_at');
    }

    /**
     * Get all documents for the client.
     */
    public function documents()
    {
        return $this->hasMany(ClientDocument::class, 'client_id')->latest();
    }

    /**
     * Get all documents uploaded by this user (staff/org).
     */
    public function uploadedDocuments()
    {
        return $this->hasMany(ClientDocument::class, 'uploaded_by_id');
    }

    /**
     * The services assigned to the client.
     */
    public function assignedServices()
    {
        return $this->belongsToMany(Service::class, 'client_service', 'user_id', 'service_id')
                    ->withPivot('start_date', 'end_date', 'status');
    }

    /**
     * The tasks instantiated for the client.
     */
    public function assignedTasks()
    {
        return $this->hasMany(AssignedTask::class, 'client_id');
    }

    /**
     * Get all of the working notes written by the user.
     */
    public function workingNotes()
    {
        return $this->hasMany(TaskWorkingNote::class);
    }

    /**
     * Get all of the comments written by the user.
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
}