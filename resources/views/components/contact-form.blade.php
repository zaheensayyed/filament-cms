{{--
    Contact form for the frontend: <x-filament-cms::contact-form />
    Publish to restyle: php artisan vendor:publish --tag=filament-cms-views
    Posts to route('filament-cms.contact.submit'). The "website" field is a honeypot:
    keep it in the form and keep it hidden.
--}}
@props([
    'phone' => true,
    'subject' => true,
    'submitLabel' => 'Send message',
])

@php
    // $errors is only shared by the "web" middleware group; keep the component usable anywhere.
    $errors ??= new \Illuminate\Support\ViewErrorBag;
@endphp

@if (\zaheensayyed\FilamentCms\Contact\ContactForm::enabled() && Route::has('filament-cms.contact.submit'))
    <form method="POST" action="{{ route('filament-cms.contact.submit') }}" {{ $attributes->merge(['class' => 'filament-cms-contact-form']) }}>
        @csrf

        @if (session(\zaheensayyed\FilamentCms\Contact\ContactForm::SESSION_KEY))
            <div class="filament-cms-contact-form__success" role="status">
                {{ session(\zaheensayyed\FilamentCms\Contact\ContactForm::SESSION_KEY) }}
            </div>
        @endif

        @error('website')
            <div class="filament-cms-contact-form__error" role="alert">{{ $message }}</div>
        @enderror

        <div class="filament-cms-contact-form__field">
            <label for="contact-name">Name</label>
            <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
            @error('name') <p class="filament-cms-contact-form__error">{{ $message }}</p> @enderror
        </div>

        <div class="filament-cms-contact-form__field">
            <label for="contact-email">Email</label>
            <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
            @error('email') <p class="filament-cms-contact-form__error">{{ $message }}</p> @enderror
        </div>

        @if ($phone)
            <div class="filament-cms-contact-form__field">
                <label for="contact-phone">Phone <small>(optional)</small></label>
                <input id="contact-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel">
                @error('phone') <p class="filament-cms-contact-form__error">{{ $message }}</p> @enderror
            </div>
        @endif

        @if ($subject)
            <div class="filament-cms-contact-form__field">
                <label for="contact-subject">Subject <small>(optional)</small></label>
                <input id="contact-subject" type="text" name="subject" value="{{ old('subject') }}" maxlength="255">
                @error('subject') <p class="filament-cms-contact-form__error">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="filament-cms-contact-form__field">
            <label for="contact-message">Message</label>
            <textarea id="contact-message" name="message" rows="5" required maxlength="5000">{{ old('message') }}</textarea>
            @error('message') <p class="filament-cms-contact-form__error">{{ $message }}</p> @enderror
        </div>

        {{-- Honeypot: hidden from people and screen readers, filled in by bots. --}}
        <div aria-hidden="true" style="position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;">
            <label for="contact-website">Leave this field empty</label>
            <input id="contact-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
        </div>

        <button type="submit">{{ $submitLabel }}</button>
    </form>
@endif
