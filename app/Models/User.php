<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'role', 'status',
        'profile_picture', 'notifications_enabled',
        'two_factor_enabled', 'theme', 'language',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'two_factor_enabled'    => 'boolean',
    ];

    // Role helpers
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isInventory(): bool  { return $this->role === 'inventory'; }
    public function isProduction(): bool { return $this->role === 'production'; }
    public function isManager(): bool    { return $this->role === 'manager'; }

    // Permissions map (mirrors your frontend RBAC)
    public function permissions(): array
    {
        $map = [
            'admin' => [
                'manageUsers', 'assignRoles', 'accessDashboard', 'manageInventory',
                'manageProduction', 'manageBatches', 'viewReports', 'viewAuditLogs',
                'editSystemConfig', 'deleteRecords', 'overrideErrors',
            ],
            'inventory' => ['accessDashboard', 'manageInventory', 'viewReports'],
            'production' => ['accessDashboard', 'manageProduction', 'manageBatches', 'viewReports'],
            'manager'  => ['accessDashboard', 'viewReports'],
        ];
        return $map[$this->role] ?? [];
    }

    public function can($permission, $arguments = []): bool
    {
        return in_array($permission, $this->permissions());
    }

    public function allowedPages(): array
    {
        $map = [
            'admin'      => ['dashboard', 'inventory', 'production', 'batches', 'reports', 'users', 'settings', 'logs'],
            'inventory'  => ['dashboard', 'inventory', 'reports'],
            'production' => ['dashboard', 'production', 'batches', 'reports'],
            'manager'    => ['dashboard', 'reports'],
        ];
        return $map[$this->role] ?? [];
    }

    public function productionBatches()
    {
        return $this->hasMany(ProductionBatch::class, 'staff_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'staff_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
