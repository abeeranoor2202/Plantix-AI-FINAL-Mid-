@extends('emails.layouts.master', [
    'heroIcon'      => '💬',
    'heroTitle'     => 'New Reply on Your Forum Thread',
    'heroSubtitle'  => $thread->title ?? 'Forum Discussion',
    'emailSubject'  => 'New reply on "' . ($thread->title ?? 'your forum thread') . '"',
    'recipientEmail'=> $recipient->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $recipient->name ?? 'Member' }}</strong>,</p>
<p>Someone has replied to your forum thread:</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Thread</span>    <span class="info-value">{{ $thread->title }}</span></div>
    <div class="info-row"><span class="info-label">Replied By</span><span class="info-value">{{ $reply->user->name ?? 'Community Member' }}</span></div>
    <div class="info-row"><span class="info-label">Category</span>  <span class="info-value">{{ $thread->category->name ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Date</span>      <span class="info-value">{{ $reply->created_at->format('d M Y, h:i A') }}</span></div>
</div>

<p><strong>Their reply:</strong></p>
<blockquote style="border-left:4px solid #c8e6c9; padding:12px 16px; background:#f9fbe7; border-radius:0 6px 6px 0; color:#444; font-style:italic; margin:0 0 20px;">
    {{ \Str::limit($reply->body, 400) }}
</blockquote>

<div class="btn-wrap">
    <a href="{{ route('forum.thread', $thread->slug) }}" class="btn">💬 View Full Thread</a>
</div>

<hr class="divider">
<p style="font-size:12px;color:#aaa;text-align:center">You received this email because you started or participated in this thread. <a href="{{ config('app.url') }}/unsubscribe" style="color:#aaa;">Unsubscribe</a></p>
@endsection
