<?php

namespace zaheensayyed\FilamentCms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use zaheensayyed\FilamentCms\FilamentCms;
use zaheensayyed\FilamentCms\Models\Post;

class BlogController
{
    public function index(): View
    {
        return view(config('filament-cms.blog.views.index'), ['posts' => FilamentCms::getPosts()]);
    }

    /**
     * Drafts and scheduled posts are 404, except for users who may edit them (a preview).
     */
    public function show(string $slug): View
    {
        $post = FilamentCms::getPost($slug);

        if ($post === null) {
            $post = Post::with('createdBy')->where('slug', $slug)->first();

            abort_unless($post && Gate::allows('update', $post), 404);
        }

        return view(config('filament-cms.blog.views.show'), ['post' => $post]);
    }
}
