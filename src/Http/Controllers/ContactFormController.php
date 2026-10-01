<?php

namespace zaheensayyed\FilamentCms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;
use zaheensayyed\FilamentCms\Contact\ContactForm;
use zaheensayyed\FilamentCms\Http\Requests\ContactFormRequest;
use zaheensayyed\FilamentCms\Mail\ContactFormSubmitted;
use zaheensayyed\FilamentCms\Models\ContactFormSubmission;

class ContactFormController
{
    public function __invoke(ContactFormRequest $request): RedirectResponse | JsonResponse
    {
        abort_unless(ContactForm::enabled(), 404);

        // Persist first, always: a mail failure must never lose the message.
        $submission = ContactFormSubmission::create([
            ...$request->safe()->only(['name', 'email', 'phone', 'subject', 'message']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->sendNotification($submission);

        $message = ContactForm::successMessage();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return back()->with(ContactForm::SESSION_KEY, $message);
    }

    /**
     * The status becomes "sent" when the mailer fires MessageSent (see the service provider)
     * and "failed" here or in ContactFormSubmitted::failed() when a queue worker sends it.
     */
    protected function sendNotification(ContactFormSubmission $submission): void
    {
        $recipients = ContactForm::recipients();

        if ($recipients === []) {
            $submission->markAsFailed('No recipient configured: set Contact Form → Recipients or Company Info → Email.');

            return;
        }

        try {
            Mail::to($recipients)->send(new ContactFormSubmitted($submission));
        } catch (Throwable $exception) {
            report($exception);

            $submission->markAsFailed($exception);
        }
    }
}
