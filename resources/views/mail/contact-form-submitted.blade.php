{{-- Publish with: php artisan vendor:publish --tag=filament-cms-views --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New contact form submission</title>
</head>
<body style="margin: 0; padding: 24px; background: #f4f4f5; font-family: -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #18181b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e4e4e7;">
        <tr>
            <td style="padding: 24px 24px 8px;">
                <h1 style="margin: 0 0 4px; font-size: 18px;">New contact form submission</h1>
                <p style="margin: 0; font-size: 13px; color: #71717a;">{{ $submission->created_at?->format('d M Y, H:i') }} · reply to this email to answer {{ $submission->name }} directly.</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: 14px;">
                    <tr>
                        <td style="padding: 6px 0; width: 90px; color: #71717a;">Name</td>
                        <td style="padding: 6px 0;">{{ $submission->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #71717a;">Email</td>
                        <td style="padding: 6px 0;"><a href="mailto:{{ $submission->email }}" style="color: #b45309;">{{ $submission->email }}</a></td>
                    </tr>
                    @if ($submission->phone)
                        <tr>
                            <td style="padding: 6px 0; color: #71717a;">Phone</td>
                            <td style="padding: 6px 0;">{{ $submission->phone }}</td>
                        </tr>
                    @endif
                    @if ($submission->subject)
                        <tr>
                            <td style="padding: 6px 0; color: #71717a;">Subject</td>
                            <td style="padding: 6px 0;">{{ $submission->subject }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 0 24px 24px;">
                <div style="padding: 16px; background: #fafafa; border-radius: 6px; border: 1px solid #f4f4f5; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $submission->message }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
