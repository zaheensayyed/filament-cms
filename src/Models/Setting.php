<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $group
 * @property string $key
 * @property mixed $value
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Setting extends Model
{
    protected $table = 'cms_settings';

    public $fillable = [
        'group',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];
}
