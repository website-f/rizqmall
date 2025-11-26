<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'subscription_user_id',
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'avatar',
        'is_active',
        'email_verified',
        'email_verified_at',
        'subscription_status',
        'subscription_expires_at',
        'auth_type',
        'bio',
        'date_of_birth',
        'gender',
        'preferences',
        'last_login_ip',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'last_login_at' => 'datetime',
        'email_verified' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed', // Laravel 11 auto-hashing
    ];

    // Relationships
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopeVendors($query)
    {
        return $query->where('user_type', 'vendor');
    }

    public function scopeCustomers($query)
    {
        return $query->where('user_type', 'customer');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSso($query)
    {
        return $query->where('auth_type', 'sso');
    }

    public function scopeLocal($query)
    {
        return $query->where('auth_type', 'local');
    }

    // Accessors & Mutators
    public function getIsVendorAttribute()
    {
        return $this->user_type === 'vendor';
    }

    public function getIsCustomerAttribute()
    {
        return $this->user_type === 'customer';
    }

    public function getIsAdminAttribute()
    {
        return $this->user_type === 'admin';
    }

    public function getIsSsoAttribute()
    {
        return $this->auth_type === 'sso';
    }

    public function getHasActiveSubscriptionAttribute()
    {
        // Local customers don't need subscription
        if ($this->is_customer && $this->auth_type === 'local') {
            return true;
        }

        // SSO vendors need active subscription
        if ($this->is_vendor && $this->auth_type === 'sso') {
            if (!$this->subscription_expires_at) {
                return false;
            }

            return $this->subscription_status === 'active' &&
                $this->subscription_expires_at->isFuture();
        }

        // Local vendors are always active
        return true;
    }

    public function getDefaultAddressAttribute()
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    public function getFullAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            return asset('defUse.jpg');
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return asset('storage/' . $this->avatar);
    }

    // Helper Methods
    public function hasStore()
    {
        return $this->stores()->exists();
    }

    public function updateLastLogin($ip = null)
    {
        $this->update([
            'last_login_ip' => $ip ?? request()->ip(),
            'last_login_at' => now(),
        ]);
    }

    public function canCreateStore()
    {
        return $this->is_vendor &&
            !$this->hasStore() &&
            $this->has_active_subscription;
    }

    public function canAccessVendorPanel()
    {
        return $this->is_vendor &&
            $this->hasStore() &&
            $this->has_active_subscription;
    }

    /**
     * Check if user can login (for local auth)
     */
    public function canLogin()
    {
        return $this->is_active && $this->password !== null;
    }

    /**
     * Set password for SSO users who want local login
     */
    public function enableLocalAuth($password)
    {
        $this->update([
            'password' => Hash::make($password),
            'auth_type' => 'local', // Switch to local auth
        ]);
    }
}
