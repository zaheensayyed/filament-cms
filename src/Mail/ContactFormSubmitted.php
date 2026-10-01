<?php

namespace zaheensayyed\FilamentCms\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;
use zaheensayyed\FilamentCms\Contact\ContactForm;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;

/**
 * Sent through the app's default mailer. Queued, so apps with a queue worker send it in the
 * background; on the default "sync" queue it is still sent inline.
 */
class ContactFormSubmitted extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ContactFormSubmission $submission) {}

    public function envelope(): Envelope
    {
        $subject = $this->submission->subject ?: 'New contact form submission';

        if ($prefix = ContactForm::subjectPrefix()) {
            $subject = "{$prefix} {$subject}";
        }

        return new Envelope(
            subject: $subject,
            replyTo: [new Address($this->submission->email, $this->submission->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'filament-cms::mail.contact-form-submitted',
            text: 'filament-cms::mail.contact-form-submitted-text',
        );
    }

    /**
     * Called by the queue when sending fails in a worker (and on the sync queue).
     */
    public function failed(Throwable $exception): void
    {
        $this->submission->markAsFailed($exception);
    }
}
