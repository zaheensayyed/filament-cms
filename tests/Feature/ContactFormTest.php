<?php

use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use zaheensayyed\FilamentCms\Contact\ContactForm;
use zaheensayyed\FilamentCms\Facades\FilamentCms;
use zaheensayyed\FilamentCms\Mail\ContactFormSubmitted;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;
use zaheensayyed\FilamentCms\Pages\Settings;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource;
use zaheensayyed\FilamentCms\Resources\ContactSubmissionResource\Pages\ListContactSubmissions;

function contactPayload(array $overrides = []): array
{
    return [
        'name' => 'Asha Patil',
        'email' => 'asha@example.com',
        'phone' => '+91 98200 00000',
        'subject' => 'Site survey',
        'message' => "Hello,\nplease call me back.",
        ...$overrides,
    ];
}

function submitContact(array $overrides = [])
{
    return test()->from('/contact')->post(route('filament-cms.contact.submit'), contactPayload($overrides));
}

beforeEach(function () {
    FilamentCms::saveSettings(['contact_form' => [
        'enabled' => true,
        'recipients' => 'office@kbi.test, sales@kbi.test',
        'subject_prefix' => '[KBI]',
        'success_message' => 'Thanks, we will call you.',
    ]]);
});

describe('submitting', function () {
    it('persists the submission and queues the email with reply-to', function () {
        Mail::fake();

        submitContact()
            ->assertRedirect('/contact')
            ->assertSessionHas(ContactForm::SESSION_KEY, 'Thanks, we will call you.');

        $submission = ContactFormSubmission::sole();

        expect($submission)
            ->name->toBe('Asha Patil')
            ->email->toBe('asha@example.com')
            ->subject->toBe('Site survey')
            ->ip_address->toBe('127.0.0.1');

        Mail::assertQueued(ContactFormSubmitted::class, function (ContactFormSubmitted $mail) use ($submission) {
            return $mail->submission->is($submission)
                && $mail->hasTo('office@kbi.test')
                && $mail->hasTo('sales@kbi.test')
                && $mail->hasReplyTo('asha@example.com', 'Asha Patil')
                && $mail->envelope()->subject === '[KBI] Site survey';
        });
    });

    it('falls back to the company email when no recipients are set', function () {
        Mail::fake();
        FilamentCms::saveSettings([
            'company' => ['email' => 'hello@kbi.test'],
            'contact_form' => ['recipients' => ''],
        ]);

        submitContact()->assertSessionHas(ContactForm::SESSION_KEY);

        Mail::assertQueued(ContactFormSubmitted::class, fn (ContactFormSubmitted $mail) => $mail->hasTo('hello@kbi.test'));
    });

    it('sends through the default mailer and marks the row as sent', function () {
        config()->set('mail.default', 'array');

        submitContact();

        expect(ContactFormSubmission::sole()->mail_status)->toBe(ContactFormSubmission::MAIL_SENT);

        $sent = app('mailer')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();

        expect($sent->getSubject())->toBe('[KBI] Site survey')
            ->and($sent->getReplyTo()[0]->getAddress())->toBe('asha@example.com')
            ->and($sent->getHtmlBody())->toContain('please call me back.')
            ->and($sent->getTextBody())->toContain('Name: Asha Patil');
    });

    it('keeps the submission and shows success when the mail transport fails', function () {
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp', [
            'transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1, 'encryption' => null,
        ]);

        submitContact()->assertSessionHas(ContactForm::SESSION_KEY, 'Thanks, we will call you.');

        expect(ContactFormSubmission::sole())
            ->mail_status->toBe(ContactFormSubmission::MAIL_FAILED)
            ->mail_error->not->toBeEmpty();
    });

    it('marks the row as failed when no recipient is configured at all', function () {
        Mail::fake();
        FilamentCms::saveSettings(['contact_form' => ['recipients' => '']]);

        submitContact()->assertSessionHas(ContactForm::SESSION_KEY);

        expect(ContactFormSubmission::sole()->mail_status)->toBe(ContactFormSubmission::MAIL_FAILED);
        Mail::assertNothingQueued();
    });

    it('answers JSON requests with 201', function () {
        Mail::fake();

        $this->postJson(route('filament-cms.contact.submit'), contactPayload())
            ->assertCreated()
            ->assertJson(['message' => 'Thanks, we will call you.']);
    });
});

describe('rejecting', function () {
    it('validates required fields, email and lengths', function () {
        submitContact(['name' => '', 'email' => 'nope', 'message' => str_repeat('a', 5001), 'phone' => str_repeat('1', 31)])
            ->assertSessionHasErrors(['name', 'email', 'message', 'phone']);

        expect(ContactFormSubmission::count())->toBe(0);
    });

    it('rejects a filled honeypot without creating a row', function () {
        Mail::fake();

        submitContact(['website' => 'http://spam.example'])->assertSessionHasErrors('website');

        expect(ContactFormSubmission::count())->toBe(0);
        Mail::assertNothingQueued();
    });

    it('rate limits to 5 submissions per minute per IP', function () {
        Mail::fake();

        foreach (range(1, 5) as $i) {
            submitContact()->assertRedirect();
        }

        submitContact()->assertStatus(429);

        expect(ContactFormSubmission::count())->toBe(5);
    });

    it('returns 404 when the form is switched off in settings', function () {
        FilamentCms::saveSettings(['contact_form' => ['enabled' => false]]);

        submitContact()->assertNotFound();

        expect(ContactFormSubmission::count())->toBe(0);
    });
});

describe('frontend component', function () {
    it('renders fields, csrf, honeypot and old input', function () {
        $this->withSession(['_old_input' => ['name' => 'Old Name']])->get('/');

        $html = (string) $this->blade('<x-filament-cms::contact-form />');

        expect($html)
            ->toContain('action="' . route('filament-cms.contact.submit') . '"')
            ->toContain('name="_token"')
            ->toContain('name="website"')
            ->toContain('name="message"');
    });

    it('shows the success message after submitting', function () {
        session()->flash(ContactForm::SESSION_KEY, 'Thanks, we will call you.');

        $this->blade('<x-filament-cms::contact-form />')->assertSee('Thanks, we will call you.');
    });

    it('renders nothing when the form is switched off', function () {
        FilamentCms::saveSettings(['contact_form' => ['enabled' => false]]);

        $this->blade('<x-filament-cms::contact-form />')->assertDontSee('<form', false);
    });
});

describe('panel', function () {
    it('lists submissions and filters by mail status and date', function () {
        $sent = ContactFormSubmission::create([...contactPayload(['name' => 'Sent One']), 'mail_status' => 'sent']);
        $failed = ContactFormSubmission::create([...contactPayload(['name' => 'Failed One']), 'mail_status' => 'failed', 'mail_error' => 'Connection refused']);
        $old = ContactFormSubmission::create(contactPayload(['name' => 'Old One']));
        $old->forceFill(['created_at' => now()->subMonth()])->save();

        Livewire::test(ListContactSubmissions::class)
            ->assertCanSeeTableRecords([$sent, $failed, $old])
            ->filterTable('mail_status', 'failed')
            ->assertCanSeeTableRecords([$failed])
            ->assertCanNotSeeTableRecords([$sent, $old]);

        Livewire::test(ListContactSubmissions::class)
            ->filterTable('created_at', ['from' => now()->subWeek()->toDateString()])
            ->assertCanSeeTableRecords([$sent, $failed])
            ->assertCanNotSeeTableRecords([$old]);
    });

    it('is read-only but can view and delete', function () {
        $submission = ContactFormSubmission::create(contactPayload());
        $other = ContactFormSubmission::create(contactPayload());

        expect(ContactSubmissionResource::canCreate())->toBeFalse()
            ->and(ContactSubmissionResource::canEdit($submission))->toBeFalse()
            ->and(array_keys(ContactSubmissionResource::getPages()))->toBe(['index']);

        Livewire::test(ListContactSubmissions::class)
            ->assertTableActionExists(ViewAction::class)
            ->assertTableActionDoesNotExist('edit')
            ->mountTableAction(ViewAction::class, $submission)
            ->assertSee('please call me back.');

        Livewire::test(ListContactSubmissions::class)->callTableAction(DeleteAction::class, $submission);

        expect(ContactFormSubmission::find($submission->id))->toBeNull();

        Livewire::test(ListContactSubmissions::class)->callTableBulkAction(DeleteBulkAction::class, [$other]);

        expect(ContactFormSubmission::count())->toBe(0);
    });

    it('shows a navigation badge for the last 7 days', function () {
        expect(ContactSubmissionResource::getNavigationBadge())->toBeNull();

        ContactFormSubmission::create(contactPayload());
        ContactFormSubmission::create(contactPayload())->forceFill(['created_at' => now()->subDays(8)])->save();

        expect(ContactSubmissionResource::getNavigationBadge())->toBe('1');
    });

    it('validates the recipients setting', function () {
        Livewire::test(Settings::class)
            ->fillForm([
                'company' => ['contact_no' => '1', 'email' => 'hello@kbi.test'],
                'contact_form' => ['recipients' => 'office@kbi.test, not-an-email'],
            ])
            ->call('save')
            ->assertHasFormErrors(['contact_form.recipients']);
    });
});
