<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['company_name', 'company_description', 'logo_path', 'theme'];

    // Always get or create the single settings row
    public static function instance(): self
    {
        return self::firstOrCreate([], [
            'company_name' => 'Naturasi Cheese Co.',
            'theme'        => 'default',
        ]);
    }
}
