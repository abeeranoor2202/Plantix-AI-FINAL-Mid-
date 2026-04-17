@extends('emails.layouts.master', [
    'heroIcon' => '📢',
    'heroTitle' => $emailSubject ?? 'Platform Announcement',
    'heroSubtitle' => config('app.name') . ' admin communication',
    'emailSubject' => $emailSubject ?? 'Platform Announcement',
])

@section('content')
@php
    $safeMessage = strip_tags((string) ($messageBody ?? ''), '<p><br><strong><b><em><i><ul><ol><li><a>');
@endphp

<p>Hi,</p>

<div style="line-height:1.7; color:#334155;">
    {!! $safeMessage !!}
</div>

<div class="alert-box alert-info">
    This message was sent by a platform administrator.
</div>
@endsection





