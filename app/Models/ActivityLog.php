<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'username', 'module', 'action', 'details', 'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper to quickly log an action from anywhere
    public static function record(string $module, string $action, string $details = ''): void
    {
        $user = auth()->user();
        self::create([
            'user_id'    => $user?->id,
            'username'   => $user?->username ?? 'System',
            'module'     => $module,
            'action'     => $action,
            'details'    => $details,
            'ip_address' => request()->ip(),
        ]);
    }
}
