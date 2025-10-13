<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'role',
        'group',
        'created_by',
        'used',
        'multi_use',
        'expires_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'multi_use' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // Отношение к пользователю, создавшему приглашение
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Проверка, истекло ли приглашение
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    // Проверка, действительно ли приглашение
    public function isValid()
    {
        return (!$this->used || $this->multi_use) && !$this->isExpired();
    }

    // Генерация нового токена
    public static function generateToken()
    {
        $token = Str::random(32);
        
        // Проверяем, что токен уникален
        while (self::where('token', $token)->exists()) {
            $token = Str::random(32);
        }
        
        return $token;
    }
}
