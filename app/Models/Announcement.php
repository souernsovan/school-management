<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'body', 'type', 'audience', 'pinned', 'expires_at', 'user_id',
    ];

    protected $casts = [
        'pinned'     => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->hasRole('Student')) {
            return $query->whereIn('audience', ['all', 'students']);
        }
        if ($user->hasRole('Teacher')) {
            return $query->whereIn('audience', ['all', 'teachers']);
        }
        return $query;
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'success' => 'emerald',
            'warning' => 'amber',
            'urgent'  => 'red',
            default   => 'blue',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'urgent'  => 'M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
            default   => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    public static function types(): array
    {
        return ['info', 'success', 'warning', 'urgent'];
    }
}
