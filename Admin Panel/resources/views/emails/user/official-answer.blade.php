@extends('emails.layouts.master', [
    'heroIcon'      => '🏅',
    'heroTitle'     => 'Expert Answer on Your Thread',
    'heroSubtitle'  => $thread->title ?? '',
    'emailSubject'  => 'Official expert answer on "' . ($thread->title ?? 'your thread') . '"',
    'recipientEmail'=> $recipient->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $recipient->name ?? 'Member' }}</strong>,</p>
<p>Your forum question has received an <strong>official expert answer</strong> from a verified agricultural specialist!</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Question</span>   <span class="info-value">{{ $thread->title }}</span></div>
    <div class="info-row"><span class="info-label">Expert</span>     <span class="info-value">{{ $reply->user->name ?? 'Expert' }}</span></div>
    <div class="info-row"><span class="info-label">Specialty</span>  <span class="info-value">{{ $reply->user->expert?->specialty ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Answered On</span><span class="info-value">{{ $reply->created_at->format('d M Y, h:i A') }}</span></div>
</div>

<p><strong>Expert Answer:</strong></p>
<div style="background:#e8f5e9; border:1px solid #c8e6c9; border-left:4px solid #2e7d32; border-radius:6px; padding:16px 20px; margin-bottom:20px;">
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:10px;">
        <span style="background:#2e7d32;color:#fff;border-radius:50%;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">✓</span>
        <strong style="color:#1b5e20;">Official Expert Answer</strong>
    </div>
    <p style="margin:0;color:#444;font-style:italic;">{{ \Str::limit($reply->body, 500) }}</p>
</div>

<div class="btn-wrap">
    <a href="{{ route('forum.thread', $thread->slug) }}" class="btn">🏅 View Full Answer</a>
</div>
@endsection
