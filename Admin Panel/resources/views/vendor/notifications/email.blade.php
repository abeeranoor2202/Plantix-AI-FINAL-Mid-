@php
    $heroByLevel = [
        'success' => ['icon' => '✅', 'title' => 'Action Completed'],
        'error' => ['icon' => '⚠️', 'title' => 'Action Required'],
    ];

    $meta = $heroByLevel[$level ?? 'default'] ?? ['icon' => '🔔', 'title' => 'Notification'];
    $subject = trim((string) ($subject ?? ($meta['title'] . ' - ' . config('app.name'))));
@endphp

@extends('emails.layouts.master', [
    'heroIcon' => $meta['icon'],
    'heroTitle' => $meta['title'],
    'heroSubtitle' => config('app.name') . ' update',
    'emailSubject' => $subject,
    'recipientEmail' => $notifiable->email ?? '',
])

@section('content')
@if (! empty($greeting))
<p>{{ $greeting }}</p>
@endif

@foreach ($introLines as $line)
<p>{{ $line }}</p>
@endforeach

@if (! empty($actionText) && ! empty($actionUrl))
<div class="btn-wrap">
    <a href="{{ $actionUrl }}" class="btn @if(($level ?? null) === 'error') btn-danger @endif">
        {{ $actionText }}
    </a>
</div>
@endif

@foreach ($outroLines as $line)
<p>{{ $line }}</p>
@endforeach

@if (! empty($salutation))
<p>{!! nl2br(e($salutation)) !!}</p>
@else
<p>Regards,<br>{{ config('app.name') }}</p>
@endif
@endsection
