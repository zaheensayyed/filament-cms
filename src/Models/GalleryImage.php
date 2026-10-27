<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $gallery_id
 * @property string $image_name
 * @property ?string $image_caption
 * @property int $created_by
 * @property ?int $updated_by
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read ?Model $createdBy
 * @property-read ?Model $updatedBy
 * @property-read ?string $image_url
 */
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
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
