# Contact form

The package ships a contact form endpoint that stores every submission, emails it to your
team through the app's **default mailer** (`config/mail.php`, e.g. your server's SMTP) and
lists it in the panel under **Contact Submissions**.

## 1. Configure it in the panel

**Settings → Contact Form**

| Setting key | Meaning |
| --- | --- |
| `contact_form.enabled` | Switch the form on/off. When off, the endpoint returns 404 and the component renders nothing. |
| `contact_form.recipients` | Comma-separated addresses. Empty → falls back to `company.email` (Settings → Company Info). |
| `contact_form.subject_prefix` | Prepended to the email subject, e.g. `[Website]`. |
| `contact_form.success_message` | Flash message shown to the visitor after sending. |

Mail is sent with `replyTo` set to the visitor, so staff can answer with "Reply".

## 2. Put the form on a page

```blade
<x-filament-cms::contact-form />

{{-- Without the optional fields, custom button text and your own classes: --}}
<x-filament-cms::contact-form :phone="false" :subject="false" submit-label="Send" class="my-form" />
```

To restyle the markup, publish the views and edit
`resources/views/vendor/filament-cms/components/contact-form.blade.php`:

```bash
php artisan vendor:publish --tag=filament-cms-views
```

## 3. Or write the HTML yourself

Post to the named route `filament-cms.contact.submit`. Keep `@csrf` and the hidden
`website` honeypot field; requests with a filled honeypot are rejected.

```blade
<form method="POST" action="{{ route('filament-cms.contact.submit') }}">
    @csrf

    @if (session('filament-cms.contact.success'))
        <p class="alert-success">{{ session('filament-cms.contact.success') }}</p>
    @endif

    <input type="text" name="name" value="{{ old('name') }}" required>
    @error('name') <p>{{ $message }}</p> @enderror

    <input type="email" name="email" value="{{ old('email') }}" required>
    @error('email') <p>{{ $message }}</p> @enderror

    <input type="tel" name="phone" value="{{ old('phone') }}">
    <input type="text" name="subject" value="{{ old('subject') }}">

    <textarea name="message" required>{{ old('message') }}</textarea>
    @error('message') <p>{{ $message }}</p> @enderror

    {{-- Honeypot: must stay empty and hidden --}}
    <input type="text" name="website" value="" tabindex="-1" autocomplete="off"
           style="position:absolute;left:-10000px" aria-hidden="true">

    <button type="submit">Send</button>
</form>
```

| Field | Rules |
| --- | --- |
| `name` | required, max 255 |
| `email` | required, valid email, max 255 |
| `phone` | optional, max 30 |
| `subject` | optional, max 255 |
| `message` | required, max 5000 |

A JSON request (`Accept: application/json`) gets `201 {"message": "..."}` instead of a redirect.

## Delivery and failures

1. The submission is saved **first**, so a mail failure never loses a message.
2. The `ContactFormSubmitted` mailable is queued (`ShouldQueue`). On the default `sync` queue it
   is sent immediately; with a queue worker it is sent in the background.
3. The row's mail status becomes `sent` once the mailer accepts it, or `failed` with the
   error message (visible in the panel). The visitor sees the success message either way.

Spam protection: the honeypot field plus a rate limit of 5 submissions per minute per IP
(`filament-cms.contact_form.rate_limit`). No captcha.

## Config (`config/filament-cms.php`)

```php
'contact_form' => [
    'enabled' => true,                 // register the POST route at all
    'path' => 'filament-cms/contact',  // URL of the endpoint
    'middleware' => ['web'],
    'rate_limit' => 5,                 // per minute, per IP
],
```

## Customising the email

The HTML and plain-text templates are publishable with the views
(`vendor/filament-cms/mail/contact-form-submitted*.blade.php`); both receive `$submission`.
