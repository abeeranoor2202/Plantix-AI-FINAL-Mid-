@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ url('notification') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    Notifications
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Broadcast</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-bullhorn text-success me-2"></i> Broadcast Notification</h1>
        </div>
    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 12px; padding: 16px; font-size: 14px; font-weight: 600; margin-bottom: 24px;">
                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; border-radius: 12px; padding: 16px; font-size: 14px; font-weight: 600; margin-bottom: 24px;">{{ session('error') }}</div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <h2 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading); margin: 0; display: flex; align-items: center; gap: 12px;">
                            <i class="fa fa-bullhorn" style="color: var(--agri-primary);"></i> Compose Broadcast
                        </h2>
                        <a href="{{ route('admin.notifications.broadcast.history') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; padding: 6px 12px; font-size: 12px;">
                            <i class="fa fa-history me-1"></i> History
                        </a>
                    </div>
                    <div style="padding: 32px 28px;">

                        <form method="POST" action="{{ route('admin.notifications.broadcast.send') }}">
                            @csrf

                            <div class="mb-4">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" maxlength="120"
                                       class="form-agri @error('title') is-invalid @enderror"
                                       placeholder="Notification title…" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">120 characters max.</div>
                            </div>

                            <div class="mb-4">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Message <span class="text-danger">*</span></label>
                                <textarea name="body" rows="5" maxlength="500"
                                          class="form-agri @error('body') is-invalid @enderror"
                                          placeholder="Write your broadcast message…" required>{{ old('body') }}</textarea>
                                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">500 characters max.</div>
                            </div>

                            <div class="mb-4">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Action URL <span style="opacity: 0.7; text-transform: none; font-weight: 500;">(optional)</span></label>
                                <input type="url" name="action_url"
                                       class="form-agri @error('action_url') is-invalid @enderror"
                                       placeholder="https://example.com/page" value="{{ old('action_url') }}">
                                @error('action_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px;">Link users are taken to when they tap the notification.</div>
                            </div>

                            <div class="mb-4">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Send To <span class="text-danger">*</span></label>
                                <select name="target"
                                        class="form-agri @error('target') is-invalid @enderror" required>
                                    @foreach($targets as $value => $label)
                                        <option value="{{ $value }}" {{ old('target') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('target')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch" style="display: flex; align-items: center; gap: 8px;">
                                    <input class="form-check-input" type="checkbox" name="send_email" value="1"
                                           id="send_email" {{ old('send_email') ? 'checked' : '' }} style="width: 40px; height: 20px; cursor: pointer;">
                                    <label class="form-check-label" for="send_email" style="font-size: 14px; font-weight: 600; color: var(--agri-text-heading); cursor: pointer; margin: 0;">
                                        Also send via Email
                                    </label>
                                </div>
                                <div style="font-size: 11px; color: var(--agri-text-muted); margin-top: 4px; padding-left: 48px;">Sends an email copy in addition to the in-app notification.</div>
                            </div>

                            <div style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; border-radius: 12px; padding: 16px; margin-bottom: 32px;">
                                <div style="display: flex; gap: 12px;">
                                    <i class="fa fa-exclamation-triangle" style="font-size: 20px; color: #D97706;"></i>
                                    <div>
                                        <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 800; color: #92400E;">Heads up!</h4>
                                        <p style="margin: 0; font-size: 13px; color: #B45309;">This will send a notification to <strong style="color: #92400E;">every active user</strong> in the selected group. Broadcasts are processed in background jobs.</p>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 12px; border-top: 1px solid var(--agri-border); padding-top: 24px; margin-top: 32px;">
                                <button type="submit" class="btn-agri btn-agri-primary" style="padding-left: 32px; padding-right: 32px;"
                                        onclick="return confirm('Send broadcast to all selected users?')">
                                    <i class="fa fa-paper-plane"></i> Send Broadcast
                                </button>
                                <a href="{{ url('notification') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; padding-left: 24px; padding-right: 24px;">
                                    Cancel
                                </a>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
