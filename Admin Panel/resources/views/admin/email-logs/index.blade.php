@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Email Logs</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Email Logs</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review queued, sent, and failed email activity.</p>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Email Type</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Recipient</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border); font-weight: 600;">{{ ucfirst(str_replace('_', ' ', $log->notification_type)) }}</td>
                            <td style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">{{ $log->recipient_email }}<div class="text-muted small">{{ $log->recipient_name ?? '—' }}</div></td>
                            <td style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">
                                @if($log->status === 'sent')
                                    <x-badge variant="success">Sent</x-badge>
                                @elseif($log->status === 'failed')
                                    <x-badge variant="danger">Failed</x-badge>
                                @else
                                    <x-badge variant="warning">Queued</x-badge>
                                @endif
                            </td>
                            <td style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">{{ optional($log->created_at)->format('M d, Y h:i A') }}</td>
                            <td class="text-end" style="padding: 16px 24px; border-bottom: 1px solid var(--agri-border);">
                                <button type="button" class="btn-agri btn-agri-outline view-email-btn" data-bs-toggle="modal" data-bs-target="#emailLogModal" data-subject="{{ e($log->subject ?? 'Email') }}" data-recipient="{{ e($log->recipient_email) }}" data-body="{{ e(data_get($log->payload, 'body', $log->subject ?? 'No preview available.')) }}" data-type="{{ e($log->notification_type) }}" style="border-radius:8px;font-weight:700; padding: 6px 12px;">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No email logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="emailLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: 1px solid var(--agri-border);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Email Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted small">Type</div>
                    <div id="email-log-type" class="fw-semibold"></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Recipient</div>
                    <div id="email-log-recipient" class="fw-semibold"></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Subject</div>
                    <div id="email-log-subject" class="fw-semibold"></div>
                </div>
                <div>
                    <div class="text-muted small">Body Preview</div>
                    <div id="email-log-body" class="p-3 rounded-3" style="background: var(--agri-bg); white-space: pre-wrap;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).on('click', '.view-email-btn', function () {
        $('#email-log-type').text($(this).data('type'));
        $('#email-log-recipient').text($(this).data('recipient'));
        $('#email-log-subject').text($(this).data('subject'));
        $('#email-log-body').text($(this).data('body'));
    });
</script>
@endsection