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
    const TYPE_GALLERY = 'gallery';
    const TYPE_CUSTOM_URL = 'custom_url';
    const TYPE_STATIC = 'static';

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
        if ($this->type == self::TYPE_PAGE) {
            return $this->belongsTo(Page::class, 'type_id');
        }

        return $this->belongsTo(Page::class);
    }

    public function gallery()
    {
        if ($this->type == self::TYPE_GALLERY) {
            return $this->belongsTo(Gallery::class, 'type_id');
        }

        return $this->belongsTo(Gallery::class);
    }

    public static function hasOptions($type)
    {
        return ($type == self::TYPE_CUSTOM_URL) ? false : true;
    }

    public function getTitleAttribute()
    {
        if ($this->page) {
            return $this->page->title;
        } elseif ($this->gallery) {
            return $this->gallery->name;
        }

        return $this->name;
    }

    public static function getAllTypeOptions()
    {
        return [
                NavigationItem::TYPE_PAGE => 'Page',
                NavigationItem::TYPE_CUSTOM_URL => 'Custom URL',
                NavigationItem::TYPE_GALLERY => 'Photo Gallery',
                NavigationItem::TYPE_STATIC => 'Static',
        ];
    }
}
