<?php

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Post;
use zaheensayyed\FilamentCms\Resources\PostResource;
use zaheensayyed\FilamentCms\Resources\PostResource\Pages\CreatePost;
use zaheensayyed\FilamentCms\Resources\PostResource\Pages\EditPost;
use zaheensayyed\FilamentCms\Resources\PostResource\Pages\ListPosts;

function blogPost(array $attributes = []): Post
{
    return Post::create([
        'title' => 'Fire Safety Basics',
        'slug' => 'fire-safety-basics',
        'body' => '<p>Check your extinguishers every month.</p>',
        'status' => Post::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
        'created_by' => 1,
        ...$attributes,
    ]);
}

describe('panel', function () {
    it('creates a draft post with a cover image and stamps the author', function () {
        Storage::fake('public');

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Choosing a CCTV system',
                'excerpt' => 'What to look for.',
                'body' => '<p>Resolution matters.</p>',
                'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 630),
            ])
            ->assertFormSet(['slug' => 'choosing-a-cctv-system', 'status' => Post::STATUS_DRAFT])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::sole();

        expect($post)
            ->slug->toBe('choosing-a-cctv-system')
            ->status->toBe(Post::STATUS_DRAFT)
            ->published_at->toBeNull()
            ->created_by->toBe(auth()->id())
            ->and($post->cover_image)->toStartWith('blog/');

        Storage::disk('public')->assertExists($post->cover_image);
    });

    it('publishes now when saved as published', function () {
        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Launch', 'slug' => 'launch', 'status' => Post::STATUS_PUBLISHED])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Post::sole())
            ->published_at->not->toBeNull()
            ->is_published->toBeTrue();
    });

    it('requires a unique, URL-safe slug', function () {
        blogPost();

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Again', 'slug' => 'fire-safety-basics'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(CreatePost::class)
            ->fillForm(['title' => 'Again', 'slug' => 'not a slug'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'alpha_dash']);
    });

    it('keeps the slug of a saved post when the title changes', function () {
        $post = blogPost();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['title' => 'Fire Safety: The Basics'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($post->fresh())
            ->title->toBe('Fire Safety: The Basics')
            ->slug->toBe('fire-safety-basics')
            ->updated_by->toBe(auth()->id());
    });

    it('lists posts with their status and deletes them', function () {
        $published = blogPost();
        $scheduled = blogPost(['slug' => 'later', 'published_at' => now()->addWeek()]);
        $draft = blogPost(['slug' => 'draft', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);

        expect(PostResource::statusLabel($published))->toBe('Published')
            ->and(PostResource::statusLabel($scheduled))->toBe('Scheduled')
            ->and(PostResource::statusLabel($draft))->toBe('Draft');

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords([$published, $scheduled, $draft])
            ->filterTable('status', Post::STATUS_DRAFT)
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$published, $scheduled])
            ->resetTableFilters()
            ->assertTableActionVisible(EditAction::class, $published)
            ->callTableAction(DeleteAction::class, $draft);

        expect(Post::count())->toBe(2);
    });

    it('has no publish date field: a post is a draft or published', function () {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('status')
            ->assertFormFieldDoesNotExist('published_at');
    });

    it('publishes selected drafts and scheduled posts now and leaves live posts alone', function () {
        $live = blogPost(['published_at' => now()->subMonth()]);
        $scheduled = blogPost(['slug' => 'later', 'published_at' => now()->addWeek()]);
        $draft = blogPost(['slug' => 'draft', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);

        Livewire::test(ListPosts::class)
            ->callTableBulkAction('publish', [$live, $scheduled, $draft])
            ->assertNotified('Published 2 posts');

        expect($scheduled->fresh()->is_published)->toBeTrue()
            ->and($draft->fresh())->is_published->toBeTrue()->updated_by->toBe(auth()->id())
            ->and($live->fresh()->published_at->toDateString())->toBe(now()->subMonth()->toDateString());
    });

    it('publishes only the selected posts', function () {
        $first = blogPost(['slug' => 'first', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);
        $second = blogPost(['slug' => 'second', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);
        $other = blogPost(['slug' => 'other', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);

        Livewire::test(ListPosts::class)
            ->callTableBulkAction('publish', [$first, $second])
            ->assertNotified('Published 2 posts');

        expect($first->fresh()->is_published)->toBeTrue()
            ->and($second->fresh()->is_published)->toBeTrue()
            ->and($other->fresh()->is_published)->toBeFalse();

        Livewire::test(ListPosts::class)
            ->callTableBulkAction('publish', [$first])
            ->assertNotified('Nothing to publish');
    });

    it('hides Publish selected from users who cannot edit posts', function () {
        Role::findByName('content_manager')->revokePermissionTo('update_post');
        $this->actingAs($this->createUser('content_manager'));
        blogPost(['status' => Post::STATUS_DRAFT, 'published_at' => null]);

        Livewire::test(ListPosts::class)
            ->assertTableBulkActionHidden('publish');
    });
});

describe('frontend', function () {
    it('lists only published posts, newest first', function () {
        blogPost(['title' => 'Older', 'slug' => 'older', 'published_at' => now()->subWeek()]);
        blogPost(['title' => 'Newer', 'slug' => 'newer', 'published_at' => now()->subHour()]);
        blogPost(['title' => 'Scheduled', 'slug' => 'scheduled', 'published_at' => now()->addDay()]);
        blogPost(['title' => 'Draft', 'slug' => 'draft', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);

        $this->get('/blog')
            ->assertOk()
            ->assertSeeInOrder(['Newer', 'Older'])
            ->assertDontSee('Scheduled')
            ->assertDontSee('>Draft<', false);

        expect(FilamentCms::latestPosts(5)->pluck('slug')->all())->toBe(['newer', 'older'])
            ->and(FilamentCms::getPosts()->total())->toBe(2);
    });

    it('paginates the list', function () {
        config()->set('filament-cms.blog.per_page', 2);

        foreach (range(1, 3) as $i) {
            blogPost(['title' => "Post {$i}", 'slug' => "post-{$i}", 'published_at' => now()->subDays($i)]);
        }

        $this->get('/blog')->assertSee('Post 1')->assertSee('Post 2')->assertDontSee('Post 3');
        $this->get('/blog?page=2')->assertSee('Post 3')->assertDontSee('Post 1');
    });

    it('shows a published post with its SEO tags', function () {
        $post = blogPost(['excerpt' => 'Monthly checks', 'cover_image' => 'https://cdn.kbi.test/cover.jpg']);

        $this->get('/blog/fire-safety-basics')
            ->assertOk()
            ->assertSee('<h1>Fire Safety Basics</h1>', false)
            ->assertSee('Check your extinguishers every month.')
            ->assertSee('<link rel="canonical" href="' . url('blog/fire-safety-basics') . '">', false)
            ->assertSee('<meta name="description" content="Monthly checks">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:image" content="https://cdn.kbi.test/cover.jpg">', false)
            ->assertSee('<meta property="article:published_time" content="' . $post->published_at->toIso8601String() . '">', false)
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"headline":"Fire Safety Basics"', false);

        expect($post->url)->toBe(url('blog/fire-safety-basics'))
            ->and(FilamentCms::blogUrl())->toBe(url('blog'));
    });

    it('prefers the post SEO fields over the excerpt and cover image', function () {
        blogPost([
            'excerpt' => 'Excerpt',
            'meta_description' => 'Meta description',
            'cover_image' => 'https://cdn.kbi.test/cover.jpg',
            'og_image' => 'https://cdn.kbi.test/share.jpg',
            'structured_data' => '{"@type":"Article"}',
        ]);

        $this->get('/blog/fire-safety-basics')
            ->assertSee('<meta name="description" content="Meta description">', false)
            ->assertSee('<meta property="og:image" content="https://cdn.kbi.test/share.jpg">', false)
            ->assertSee('<script type="application/ld+json">{"@type":"Article"}</script>', false)
            ->assertDontSee('BlogPosting');
    });

    it('hides drafts and scheduled posts from visitors but lets editors preview them', function () {
        blogPost(['slug' => 'draft', 'status' => Post::STATUS_DRAFT, 'published_at' => null]);
        blogPost(['slug' => 'scheduled', 'published_at' => now()->addDay()]);

        $this->get('/blog/draft')->assertOk()->assertSee('Preview');
        $this->get('/blog/scheduled')->assertOk()->assertSee('Preview');

        $this->actingAs($this->createUser());
        $this->get('/blog/draft')->assertNotFound();

        auth()->logout();
        $this->get('/blog/draft')->assertNotFound();
        $this->get('/blog/scheduled')->assertNotFound();
        $this->get('/blog/missing')->assertNotFound();

        expect(FilamentCms::getPost('draft'))->toBeNull();
    });

    it('shows a post once its publish date passes', function () {
        blogPost(['slug' => 'scheduled', 'published_at' => now()->addHour()]);

        auth()->logout();
        $this->get('/blog/scheduled')->assertNotFound();

        $this->travel(2)->hours();
        $this->get('/blog/scheduled')->assertOk();
    });
});
