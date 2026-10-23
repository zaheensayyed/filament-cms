<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use zaheensayyed\FilamentCms\FilamentCms;

class Gallery extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'slug',
        'description',
        'page_id',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        // Menu titles/links come from pages and galleries, so cached menus must be rebuilt.
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

    public function images()
    {
        return $this->hasMany(GalleryImage::class, 'gallery_id');
    }

    /**
     * The page this gallery is shown on, if any.
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
