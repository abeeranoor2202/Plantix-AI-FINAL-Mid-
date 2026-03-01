@extends('emails.layouts.master', [
    'heroIcon'      => '❓',
    'heroTitle'     => 'New Forum Question in Your Domain',
    'heroSubtitle'  => $thread->category->name ?? 'Agriculture Forum',
    'emailSubject'  => 'New question: "' . \Str::limit($thread->title, 60) . '"',
    'recipientEmail'=> $expert->user->email ?? '',
])

@section('content')
<p>Hi <strong>{{ $expert->user->name ?? 'Expert' }}</strong>,</p>
<p>A new question has been posted in your area of expertise (<strong>{{ $expert->specialty ?? $thread->category->name ?? 'Agriculture' }}</strong>). Farmers value expert insights!</p>

<div class="info-box">
    <div class="info-row"><span class="info-label">Question</span>   <span class="info-value">{{ $thread->title }}</span></div>
    <div class="info-row"><span class="info-label">Category</span>   <span class="info-value">{{ $thread->category->name ?? '—' }}</span></div>
    <div class="info-row"><span class="info-label">Asked By</span>   <span class="info-value">{{ $thread->user->name ?? 'Community Member' }}</span></div>
    <div class="info-row"><span class="info-label">Posted On</span>  <span class="info-value">{{ $thread->created_at->format('d M Y, h:i A') }}</span></div>
    <div class="info-row"><span class="info-label">Replies</span>    <span class="info-value">{{ $thread->replies_count ?? 0 }}</span></div>
</div>

@if($thread->body)
<p><strong>Question Preview:</strong></p>
<blockquote style="border-left:4px solid #c8e6c9; padding:12px 16px; background:#f9fbe7; border-radius:0 6px 6px 0; color:#444; font-style:italic; margin:0 0 20px;">
    {{ \Str::limit($thread->body, 400) }}
</blockquote>
@endif

<div class="alert-box alert-info">💡 Your expert answer will be marked as an <strong>Official Answer</strong> and highlighted for the community.</div>

<div class="btn-wrap">
    <a href="{{ route('expert.forum.show', $thread->id) }}" class="btn">💬 Answer This Question</a>
</div>
@endsection
