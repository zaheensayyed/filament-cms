<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use zaheensayyed\FilamentCms\FilamentCms;

class Navigation extends Model
{
    use HasFactory;

    public $fillable = [
        'key',
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => FilamentCms::forgetMenuCache());
        static::deleted(fn () => FilamentCms::forgetMenuCache());
    }

    public function createdBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    public function items()
    {
        return $this->hasMany(NavigationItem::class, 'navigation_id')->where('level', 1);
    }
}
