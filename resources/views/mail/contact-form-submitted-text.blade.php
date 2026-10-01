New contact form submission ({{ $submission->created_at?->format('d M Y, H:i') }})

Name: {!! $submission->name !!}
Email: {!! $submission->email !!}
@if ($submission->phone)
Phone: {!! $submission->phone !!}
@endif
@if ($submission->subject)
Subject: {!! $submission->subject !!}
@endif

{!! $submission->message !!}

Reply to this email to answer {!! $submission->name !!} directly.
