<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'court_owner_id',
        'permissions',
        'is_active',
        'phone',
        'password',
    ];

    /**
     * Available modules that court owner can assign to admin assistant.
     */
    public const MODULE_SCHEDULE = 'schedule';
    public const MODULE_APPROVALS = 'approvals';
    public const MODULE_COURTS = 'courts';
    public const MODULE_PHOTOS = 'photos';
    public const MODULE_SETTINGS = 'settings';
    public const MODULE_USERS = 'users';

    public static function availableModules(): array
    {
        return [
            self::MODULE_SCHEDULE => [
                'name' => 'Viewing of Schedule',
                'description' => 'View court schedule, today\'s slots, occupancy, and reservations ledger',
                'default' => true,
                'icon' => 'fa-calendar-days',
            ],
            self::MODULE_APPROVALS => [
                'name' => 'Approval of Reservation',
                'description' => 'Review customer uploaded payment receipts, approve or reject reservations',
                'default' => true,
                'icon' => 'fa-file-invoice-dollar',
            ],
            self::MODULE_COURTS => [
                'name' => 'Courts & Pricing',
                'description' => 'Manage court details, hourly rates, player capacity, and active status',
                'default' => false,
                'icon' => 'fa-table-tennis-paddle-ball',
            ],
            self::MODULE_PHOTOS => [
                'name' => 'Website Photos',
                'description' => 'Upload, replace, and delete facility gallery photos on the public website',
                'default' => false,
                'icon' => 'fa-images',
            ],
            self::MODULE_USERS => [
                'name' => 'User Management',
                'description' => 'View, create, and manage registered player profiles, staff accounts, and account statuses',
                'default' => false,
                'icon' => 'fa-users',
            ],
            self::MODULE_SETTINGS => [
                'name' => 'Payment & Center Config',
                'description' => 'Configure venue settings, payment gateways (Xendit/PayMongo/Manual), and holding rules',
                'default' => false,
                'icon' => 'fa-sliders',
            ],
        ];
    }

    public static function defaultAssistantPermissions(): array
    {
        return [
            self::MODULE_SCHEDULE,
            self::MODULE_APPROVALS,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'court_owner';
    }

    public function isAdminAssistant(): bool
    {
        return $this->role === 'admin_assistant';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isStaffOrAdmin(): bool
    {
        return in_array($this->role, ['admin', 'court_owner', 'admin_assistant']);
    }

    public function canManageStaff(): bool
    {
        return in_array($this->role, ['admin', 'court_owner']);
    }

    /**
     * Check if user has permission to access a specific module
     */
    public function hasModuleAccess(string $module): bool
    {
        // Admin and Court Owner have full access to everything
        if ($this->isAdmin() || $this->isOwner()) {
            return true;
        }

        if (!$this->isAdminAssistant()) {
            return false;
        }

        if (!$this->is_active) {
            return false;
        }

        $perms = $this->permissions ?? [];
        if (!is_array($perms)) {
            $perms = json_decode($perms, true) ?? [];
        }

        // Schedule covers dashboard overview and all bookings list/calendar
        if (in_array($module, ['schedule', 'dashboard', 'bookings'])) {
            return in_array('schedule', $perms) || in_array('dashboard', $perms) || in_array('bookings', $perms);
        }

        return in_array($module, $perms);
    }

    /**
     * Get the first route name an admin assistant is allowed to visit
     */
    public function getFirstAllowedRoute(): string
    {
        if ($this->hasModuleAccess('schedule')) {
            return 'owner.dashboard';
        }
        if ($this->hasModuleAccess('approvals')) {
            return 'owner.approvals';
        }
        if ($this->hasModuleAccess('courts')) {
            return 'owner.courts.index';
        }
        if ($this->hasModuleAccess('photos')) {
            return 'owner.photos.index';
        }
        if ($this->hasModuleAccess('users')) {
            return 'owner.users.index';
        }
        if ($this->hasModuleAccess('settings')) {
            return 'owner.settings';
        }
        return 'home';
    }

    public function courtOwner()
    {
        return $this->belongsTo(User::class, 'court_owner_id');
    }

    public function adminAssistants()
    {
        return $this->hasMany(User::class, 'court_owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingSlots()
    {
        return $this->hasManyThrough(BookingSlot::class, Booking::class);
    }

    public function heldBookings()
    {
        return $this->hasMany(Booking::class)->whereIn('booking_status', ['held', 'expired', 'cancelled']);
    }

    /**
     * Total number of held timeslots for this user (including currently held, expired, and cancelled holds).
     * Corresponds to reservations placed on hold where payment was not completed or is in-progress.
     */
    public function getHeldTimeslotsCountAttribute(): int
    {
        if (array_key_exists('held_timeslots_count', $this->attributes) && $this->attributes['held_timeslots_count'] !== null) {
            return (int) $this->attributes['held_timeslots_count'];
        }

        return (int) $this->bookings()
            ->whereIn('booking_status', ['held', 'expired', 'cancelled'])
            ->sum('total_hours');
    }

    /**
     * Number of currently active held timeslots (within holding window, not yet expired)
     */
    public function getActiveHeldSlotsCountAttribute(): int
    {
        if (array_key_exists('active_held_slots_count', $this->attributes) && $this->attributes['active_held_slots_count'] !== null) {
            return (int) $this->attributes['active_held_slots_count'];
        }

        return (int) $this->bookings()
            ->where('booking_status', 'held')
            ->where('held_until', '>', now())
            ->sum('total_hours');
    }

    /**
     * Total count of distinct held booking sessions
     */
    public function getHeldBookingsCountAttribute(): int
    {
        if (array_key_exists('held_bookings_count', $this->attributes) && $this->attributes['held_bookings_count'] !== null) {
            return (int) $this->attributes['held_bookings_count'];
        }

        return (int) $this->bookings()
            ->whereIn('booking_status', ['held', 'expired', 'cancelled'])
            ->count();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
