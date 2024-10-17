<?php

namespace zaheensayyed\FilamentCms\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavigationItem extends Model
{
    use HasFactory;

    const TYPE_PAGE = 'page';
    const TYPE_CATEGORY_LIST = 'category_list';
    const TYPE_CUSTOM_URL = 'custom_url';

    public $fillable = [
        'navigation_id',
        'parent_id',
        'name',
        'slug',
        'level',
        'type',
        'type_id',
        'custom_url',
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

    public function childItems()
    {
        return $this->hasMany(NavigationItem::class, 'parent_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'type_id');
    }

    public static function hasOptions($type){
        return (self::TYPE_CUSTOM_URL == $type) ? false : true;
    }
}
