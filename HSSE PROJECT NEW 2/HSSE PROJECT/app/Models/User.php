<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Canonical internal role constants
    public const ROLE_ADMIN_MANAGEMENT = 'admin_management';
    public const ROLE_ADMIN_INVENTORY = 'admin_inventory';
    public const ROLE_USER = 'user';

    // Human-readable role labels
    public const ROLES = [
        self::ROLE_ADMIN_MANAGEMENT => 'Admin Management',
        self::ROLE_ADMIN_INVENTORY => 'Admin Inventory',
        self::ROLE_USER => 'User',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

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
        ];
    }

    /**
     * Check if user has specific role or any of the given roles.
     */
    public function hasRole(string|array $roles): bool
    {
        $currentRole = strtolower(trim((string) $this->role));
        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $r) {
            $rLower = strtolower(trim($r));
            if ($currentRole === $rLower) {
                return true;
            }
            // Backward compatibility for legacy 'Admin' string
            if ($rLower === self::ROLE_ADMIN_MANAGEMENT && $currentRole === 'admin') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is an Admin Management (Super Admin).
     */
    public function isAdminManagement(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN_MANAGEMENT);
    }

    /**
     * Check if user is an Admin Inventory.
     */
    public function isAdminInventory(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN_INVENTORY);
    }

    /**
     * Check if user is a standard User (read-only).
     */
    public function isUser(): bool
    {
        return $this->hasRole(self::ROLE_USER);
    }

    /**
     * Check if user is an Administrator (alias for isAdminManagement).
     */
    public function isAdmin(): bool
    {
        return $this->isAdminManagement();
    }

    /**
     * Check if user can manage users (User Management).
     */
    public function canManageUsers(): bool
    {
        return $this->isAdminManagement();
    }

    /**
     * Check if user can manage Inventory (write/create/edit/delete/transact).
     */
    public function canManageInventory(): bool
    {
        return $this->isAdminManagement() || $this->isAdminInventory();
    }

    /**
     * Check if user can manage (modify data in) a given module.
     */
    public function canManageModule(string $module = ''): bool
    {
        if (strtolower($module) === 'inventory') {
            return $this->canManageInventory();
        }
        return $this->isAdminManagement();
    }

    /**
     * Get human-readable role label.
     */
    public function getRoleLabelAttribute(): string
    {
        $key = strtolower(trim((string) $this->role));
        if ($key === 'admin') {
            return 'Admin Management';
        }
        return self::ROLES[$key] ?? (ucwords(str_replace('_', ' ', $this->role)) ?: 'User');
    }

    /**
     * Check if user account is Active.
     */
    public function isActive(): bool
    {
        $status = strtolower((string) $this->status);
        return $status === 'aktif' || $status === 'active' || empty($status);
    }

    /**
     * Check if user is the primary Admin Management account.
     */
    public function isPrimaryAdmin(): bool
    {
        return strtolower(trim((string) $this->email)) === 'admin@geodipa.co.id';
    }

    /**
     * Check if user can export PDF.
     * Admin Management: Yes, User: Yes, Admin Inventory: No.
     */
    public function canExportPdf(): bool
    {
        return $this->isAdminManagement() || $this->isUser();
    }
}
