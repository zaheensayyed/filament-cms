<?php

namespace zaheensayyed\FilamentCms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    use HasFactory;

    public $fillable = [
        'gallery_id',
        'image_name',
        'image_caption',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Uses the same disk Filament uploads to, so S3 and other non-default disks work.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image_name)) {
            return null;
        }

        return Storage::disk(config('filament.default_filesystem_disk'))->url($this->image_name);
    }
}
