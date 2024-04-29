<?php

namespace zaheensayyed\FilamentCms\Repositories;

use zaheensayyed\FilamentCms\Models\Gallery;
use zaheensayyed\FilamentCms\Models\GalleryImage;

class GalleryRepository {

    public static function storeImages(Gallery $gallery, Array $images): void
    {

        foreach($images as $imagesId => $image){
            
            $galleryImage = new GalleryImage;
            $galleryImage->created_by = auth()->id();
            $galleryImage->gallery_id = $gallery->id;
            $galleryImage->image_name = $image;
            $galleryImage->updated_by = auth()->id();
            $galleryImage->save();
        }
    }

    public static function mutateBeforeFill(Array $data): Array
    {
        // $galleryImages = GalleryImage::where('gallery_id', $data['id'])->get();
        // foreach($galleryImages as $galleryImage){
        //     $data['images']["{$galleryImage->id}"] = $galleryImage->image_name;
        // }
        
        // dd($data);
        return $data;
    }
}