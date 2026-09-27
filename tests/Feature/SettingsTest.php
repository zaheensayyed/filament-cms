<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Models\Setting;
use zaheensayyed\FilamentCms\Pages\Settings;

$companyInfo = [
    'contact_no' => '+91 98765 43210',
    'email' => 'hello@kbi.test',
    'address' => '221B Baker Street',
    'description' => 'We build things.',
];

it('saves the company info tab', function () use ($companyInfo) {
    Livewire::test(Settings::class)
        ->fillForm(['company' => $companyInfo])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::where('group', 'company')->pluck('value', 'key')->all())
        ->toEqual($companyInfo);
});

it('validates the company info fields', function () {
    Livewire::test(Settings::class)
        ->fillForm(['company' => [
            'contact_no' => str_repeat('1', 31),
            'email' => 'not-an-email',
            'address' => str_repeat('a', 501),
            'description' => str_repeat('a', 1001),
        ]])
        ->call('save')
        ->assertHasFormErrors([
            'company.contact_no' => 'max',
            'company.email' => 'email',
            'company.address' => 'max',
            'company.description' => 'max',
        ]);

    Livewire::test(Settings::class)
        ->fillForm(['company' => ['contact_no' => null, 'email' => null]])
        ->call('save')
        ->assertHasFormErrors([
            'company.contact_no' => 'required',
            'company.email' => 'required',
        ]);

    expect(Setting::count())->toBe(0);
});

it('loads saved values back into the form', function () use ($companyInfo) {
    FilamentCms::saveSettings(['company' => $companyInfo]);

    Livewire::test(Settings::class)
        ->assertFormSet(['company' => $companyInfo]);
});

it('retrieves saved settings through the facade', function () use ($companyInfo) {
    FilamentCms::saveSettings(['company' => $companyInfo]);

    expect(FilamentCms::setting('company.email'))->toBe('hello@kbi.test')
        ->and(FilamentCms::setting('company.contact_no'))->toBe('+91 98765 43210')
        ->and(FilamentCms::setting('company'))->toEqual($companyInfo);
});

it('falls back to the default for missing keys', function () {
    expect(FilamentCms::setting('company.email'))->toBeNull()
        ->and(FilamentCms::setting('company.email', 'fallback@kbi.test'))->toBe('fallback@kbi.test')
        ->and(FilamentCms::setting('unknown.group', 'x'))->toBe('x');
});

it('runs at most one query and zero when cached', function () use ($companyInfo) {
    FilamentCms::saveSettings(['company' => $companyInfo]);

    DB::enableQueryLog();

    FilamentCms::setting('company.email');
    FilamentCms::setting('company.address');
    FilamentCms::setting('company.missing', 'x');

    expect(DB::getQueryLog())->toHaveCount(1);

    DB::flushQueryLog();

    FilamentCms::setting('company.email');

    expect(DB::getQueryLog())->toHaveCount(0);
});

it('invalidates the cache on save', function () use ($companyInfo) {
    FilamentCms::saveSettings(['company' => $companyInfo]);

    expect(FilamentCms::setting('company.email'))->toBe('hello@kbi.test')
        ->and(Cache::has(zaheensayyed\FilamentCms\FilamentCms::SETTINGS_CACHE_KEY))->toBeTrue();

    Livewire::test(Settings::class)
        ->fillForm(['company' => [...$companyInfo, 'email' => 'new@kbi.test']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(FilamentCms::setting('company.email'))->toBe('new@kbi.test');
});
