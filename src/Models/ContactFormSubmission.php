<?php

namespace zaheensayyed\FilamentCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class ContactFormSubmission extends Model
{
    const MAIL_PENDING = 'pending';

    const MAIL_SENT = 'sent';

    const MAIL_FAILED = 'failed';

    public $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'mail_status',
        'mail_error',
    ];

    protected $attributes = [
        'mail_status' => self::MAIL_PENDING,
    ];

    public function markAsSent(): void
    {
        $this->forceFill(['mail_status' => self::MAIL_SENT, 'mail_error' => null])->save();
    }

    public function markAsFailed(Throwable | string $error): void
    {
        $message = $error instanceof Throwable ? $error->getMessage() : $error;

        $this->forceFill([
            'mail_status' => self::MAIL_FAILED,
            'mail_error' => Str::limit($message, 2000),
        ])->save();
    }
}
