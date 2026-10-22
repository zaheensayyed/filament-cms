<?php

use Filament\Tables\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Client;
use zaheensayyed\FilamentCms\Models\Navigation;
use zaheensayyed\FilamentCms\Models\NavigationItem;
use zaheensayyed\FilamentCms\Resources\ClientResource\Pages\CreateClient;
use zaheensayyed\FilamentCms\Resources\ClientResource\Pages\EditClient;
use zaheensayyed\FilamentCms\Resources\ClientResource\Pages\ListClients;

function cmsClient(array $attributes = []): Client
{
    return Client::create([
        'name' => 'Acme Towers',
        'slug' => 'acme-towers',
        'description' => 'Fire alarm and CCTV for a 40-floor residential tower.',
        'logo' => 'https://cdn.kbi.test/acme-logo.png',
        'created_by' => 1,
        ...$attributes,
    ]);
}

describe('panel', function () {
    it('creates a client with a logo, published and at the end of the list', function () {
        Storage::fake('public');
        cmsClient(['sort_order' => 4]);

        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => 'Blue Mall',
                'description' => 'Access control for 120 shops.',
                'logo' => UploadedFile::fake()->image('blue-mall.png'),
                'is_featured' => true,
            ])
            ->assertFormSet(['is_published' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $client = Client::where('slug', 'blue-mall')->sole();

        expect($client)
            ->is_featured->toBeTrue()
            ->is_published->toBeTrue()
            ->sort_order->toBe(5)
            ->created_by->toBe(auth()->id())
            ->and($client->logo)->toStartWith('clients/logos/')
            ->and($client->logo_url)->toBe(Storage::disk('public')->url($client->logo));

        Storage::disk('public')->assertExists($client->logo);
    });

    it('requires a title and generates a unique slug from it', function () {
        cmsClient();

        Livewire::test(CreateClient::class)
            ->fillForm(['name' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);

        Livewire::test(CreateClient::class)
            ->fillForm(['name' => 'Acme Towers'])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Client::orderBy('id')->pluck('slug')->all())->toBe(['acme-towers', 'acme-towers-2']);
    });

    it('edits a client and keeps its slug', function () {
        $client = cmsClient();

        Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
            ->fillForm(['name' => 'Acme Towers Phase 2', 'industry' => 'Residential'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($client->fresh())
            ->name->toBe('Acme Towers Phase 2')
            ->slug->toBe('acme-towers')
            ->industry->toBe('Residential')
            ->updated_by->toBe(auth()->id());
    });

    it('marks clients as featured from the list, filters, reorders and deletes them', function () {
        $first = cmsClient(['sort_order' => 1]);
        $second = cmsClient(['name' => 'Blue Mall', 'slug' => 'blue-mall', 'sort_order' => 2]);

        Livewire::test(ListClients::class)
            ->assertCanSeeTableRecords([$first, $second], inOrder: true)
            ->call('updateTableColumnState', 'is_featured', (string) $second->getKey(), true)
            ->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$second])
            ->assertCanNotSeeTableRecords([$first])
            ->resetTableFilters()
            ->call('reorderTable', [(string) $second->getKey(), (string) $first->getKey()])
            ->callTableAction(DeleteAction::class, $first);

        expect($second->fresh())
            ->is_featured->toBeTrue()
            ->sort_order->toBe(1)
            ->and(Client::count())->toBe(1);
    });

    it('lets a content manager manage and reorder clients', function () {
        $this->actingAs($this->createUser('content_manager'));

        Livewire::test(CreateClient::class)
            ->fillForm(['name' => 'Blue Mall'])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(auth()->user()->can('reorder', Client::class))->toBeTrue();
    });
});

describe('frontend', function () {
    it('lists published clients in the panel order with their logo, title and description', function () {
        cmsClient(['name' => 'Second', 'slug' => 'second', 'sort_order' => 2]);
        cmsClient(['name' => 'First', 'slug' => 'first', 'sort_order' => 1]);
        cmsClient(['name' => 'Hidden', 'slug' => 'hidden', 'is_published' => false]);

        $this->get('/clients')
            ->assertOk()
            ->assertSeeInOrder(['First', 'Second'])
            ->assertSee('https://cdn.kbi.test/acme-logo.png')
            ->assertSee('Fire alarm and CCTV for a 40-floor residential tower.')
            ->assertDontSee('Hidden');

        expect(FilamentCms::getClients()->pluck('slug')->all())->toBe(['first', 'second'])
            ->and(FilamentCms::clientsUrl())->toBe(url('clients'));
    });

    it('returns featured clients for the home page', function () {
        cmsClient(['name' => 'Featured B', 'slug' => 'featured-b', 'is_featured' => true, 'sort_order' => 2]);
        cmsClient(['name' => 'Featured A', 'slug' => 'featured-a', 'is_featured' => true, 'sort_order' => 1]);
        cmsClient(['name' => 'Featured hidden', 'slug' => 'featured-hidden', 'is_featured' => true, 'is_published' => false]);
        cmsClient(['name' => 'Regular', 'slug' => 'regular']);

        expect(FilamentCms::featuredClients()->pluck('slug')->all())->toBe(['featured-a', 'featured-b'])
            ->and(FilamentCms::featuredClients(1)->pluck('slug')->all())->toBe(['featured-a']);
    });

    it('shows every client as a card on one page, with no pages per client and nothing in the menu', function () {
        $client = cmsClient();
        cmsClient(['name' => 'Blue Mall', 'slug' => 'blue-mall', 'description' => null, 'logo' => 'https://cdn.kbi.test/mall-logo.png']);

        $navigation = Navigation::create(['key' => 'main-menu', 'name' => 'Main Menu', 'description' => '-', 'created_by' => 1]);
        NavigationItem::create(['navigation_id' => $navigation->id, 'name' => 'Clients', 'slug' => 'clients', 'level' => 1, 'type' => NavigationItem::TYPE_CUSTOM_URL, 'custom_url' => '/clients', 'created_by' => 1]);

        $this->get('/clients')
            ->assertOk()
            ->assertSeeInOrder(['Acme Towers', 'Blue Mall'])
            ->assertSee('https://cdn.kbi.test/acme-logo.png')
            ->assertSee('https://cdn.kbi.test/mall-logo.png');

        $this->get('/clients/acme-towers')->assertNotFound();

        $item = FilamentCms::getMenu('main-menu')->sole();

        expect($client->url)->toBe(url('clients'))
            ->and($item->url)->toBe(url('clients'))
            ->and($item->childItems)->toBeEmpty();
    });
});
