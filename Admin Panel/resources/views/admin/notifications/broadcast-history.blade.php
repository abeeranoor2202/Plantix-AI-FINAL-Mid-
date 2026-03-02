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
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Broadcast History</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-history text-success me-2"></i> Broadcast History</h1>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('admin.notifications.broadcast') }}" class="btn-agri btn-agri-primary" style="text-decoration: none;">
                <i class="fa fa-bullhorn me-2"></i> New Broadcast
            </a>
        </div>
    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 12px; padding: 16px; font-size: 14px; font-weight: 600; margin-bottom: 24px;">
                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="card-agri" style="padding: 0; overflow: hidden;">
            <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--agri-text-heading); margin: 0;">Past Broadcasts</h2>
                </div>
                <span style="background: var(--agri-bg); color: var(--agri-text-muted); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800;">Last 50 grouped by day</span>
            </div>
            <div class="table-responsive">
                @if($history->isEmpty())
                    <div style="padding: 60px 24px; text-align: center; color: var(--agri-text-muted);">
                        <i class="fa fa-bullhorn" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                        <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No broadcasts sent yet.</p>
                        <div style="margin-top: 24px;">
                            <a href="{{ route('admin.notifications.broadcast') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                                Send your first broadcast
                            </a>
                        </div>
                    </div>
                @else
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Title (first in day)</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Recipients</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $row)
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 18px 24px; font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">
                                    {{ $row['date'] }}
                                </td>
                                <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-main);">
                                    {{ $row['title'] }}
                                </td>
                                <td style="padding: 18px 24px; text-align: center;">
                                    <span style="background: #D1FAE5; color: #065F46; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid #A7F3D0;">
                                        {{ number_format($row['count']) }} users
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>

@endsection
