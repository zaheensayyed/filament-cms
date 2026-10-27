<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use zaheensayyed\FilamentCms\FilamentCms;

/**
 * @property int $id
 * @property ?string $key
 * @property string $name
 * @property string $description
 * @property int $created_by
 * @property ?int $updated_by
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read ?Model $createdBy
 * @property-read ?Model $updatedBy
 * @property-read Collection<int, NavigationItem> $items
 */
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    /**
     * The navigations the panel manages (key => name), from config "filament-cms.navigations".
     *
     * @return array<string, string>
     */
    public static function fixed(): array
    {
        return config('filament-cms.navigations', [
            'main-menu' => 'Main Menu',
            'footer-menu' => 'Footer Menu',
        ]);
    }

    /**
     * Creates the configured navigations that don't exist yet. A navigation saved before keys
     * existed is matched by name and given its key instead of being duplicated.
     */
    public static function ensureFixed(): void
    {
        foreach (static::fixed() as $key => $name) {
            if (static::query()->where('key', $key)->exists()) {
                continue;
            }

            $navigation = static::query()->whereNull('key')->where('name', $name)->first();

            if ($navigation) {
                $navigation->update(['key' => $key]);

                continue;
            }

            static::create([
                'key' => $key,
                'name' => $name,
                'description' => $name,
                'created_by' => auth()->id() ?? 0,
            ]);
        }
    }

    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'navigation_id')->where('level', 1);
    }
}
