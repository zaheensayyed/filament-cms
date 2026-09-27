<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Model;

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
