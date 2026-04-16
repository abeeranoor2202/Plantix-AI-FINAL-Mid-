@extends('vendor.layouts.app')

@section('title', 'Return Request #' . $return->id)

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap" style="gap: 12px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin: 0;">Return Request #{{ $return->id }}</h1>
            <p class="text-muted mb-0">Review return details and provide a clear vendor decision.</p>
        </div>
        <div class="d-flex align-items-center" style="gap: 10px;">
            <x-badge :variant="$return->status_badge_variant">{{ strtoupper(str_replace('_', ' ', $return->status)) }}</x-badge>
            <x-button :href="route('vendor.returns.index')" variant="outline" icon="fas fa-arrow-left">Back</x-button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <x-card>
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Return Context</h4>
                </x-slot>

                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Order</div>
                                <div class="fw-bold text-dark">{{ $return->order->order_number ?? ('#' . $return->order_id) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Customer</div>
                                <div class="fw-bold text-dark">{{ $return->user->name ?? 'Unknown customer' }}</div>
                                <div class="small text-muted">{{ $return->user->email ?? 'No email' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Selected Return Reason</div>
                                <div class="fw-bold text-dark">{{ $return->reason->name ?? 'Not selected' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Requested Date</div>
                                <div class="fw-bold text-dark">{{ $return->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Customer Message</div>
                                <div class="text-dark">{{ $return->notes ?: 'No message provided by customer.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card class="mt-4">
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Returned Items</h4>
                </x-slot>

                <x-table>
                    <thead style="background: var(--agri-bg);">
                        <tr>
                            <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Product</th>
                            <th style="padding: 14px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;" class="text-end">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($return->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-dark">{{ $item->product->name ?? 'Product unavailable' }}</td>
                                <td class="px-4 py-3 text-end fw-bold">{{ $item->quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No line-item breakdown available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </x-card>

            @if($return->status === \App\Models\ReturnRequest::STATUS_PENDING)
                <x-card class="mt-4">
                    <x-slot name="header">
                        <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Take Action</h4>
                    </x-slot>

                    <div class="p-4">
                        <p class="text-muted mb-3">Approve with resolution type or reject with a clear reason.</p>
                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveReturnModal">Approve Return</button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectReturnModal">Reject Return</button>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        <div class="col-lg-4">
            <x-card>
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Timeline</h4>
                </x-slot>

                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <div class="fw-bold text-dark">Created</div>
                            <div class="small text-muted">{{ $return->created_at->format('M d, Y h:i A') }}</div>
                        </li>

                        @if($return->vendor_responded_at)
                            <li class="list-group-item px-0">
                                <div class="fw-bold text-dark">Vendor Responded</div>
                                <div class="small text-muted">{{ $return->vendor_responded_at->format('M d, Y h:i A') }}</div>
                                @if($return->resolution_label)
                                    <div class="small text-muted">Resolution: {{ $return->resolution_label }}</div>
                                @endif
                            </li>
                        @endif

                        @if($return->isRejected())
                            <li class="list-group-item px-0">
                                <div class="fw-bold text-dark">Final Resolution</div>
                                <div class="small text-muted">Rejected</div>
                            </li>
                        @endif

                        @if($return->isCompleted())
                            <li class="list-group-item px-0">
                                <div class="fw-bold text-dark">Final Resolution</div>
                                <div class="small text-muted">Completed{{ $return->completed_at ? ' at ' . $return->completed_at->format('M d, Y h:i A') : '' }}</div>
                            </li>
                        @endif
                    </ul>
                </div>
            </x-card>

            @if($return->rejection_reason)
                <x-card class="mt-4">
                    <div class="p-4">
                        <h5 class="mb-2">Rejection Reason</h5>
                        <div class="text-muted">{{ $return->rejection_reason }}</div>
                    </div>
                </x-card>
            @endif

            @if($return->vendor_response_notes)
                <x-card class="mt-4">
                    <div class="p-4">
                        <h5 class="mb-2">Vendor Notes</h5>
                        <div class="text-muted">{{ $return->vendor_response_notes }}</div>
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</div>

@if($return->status === \App\Models\ReturnRequest::STATUS_PENDING)
    <div class="modal fade" id="approveReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('vendor.returns.approve', $return->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Return Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Resolution Type</label>
                        <select name="resolution_type" class="form-agri" required>
                            <option value="">Select resolution</option>
                            <option value="refund">Refund</option>
                            <option value="replace">Replace</option>
                            <option value="store_credit">Store Credit</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-muted small">Notes (optional)</label>
                        <textarea name="response_notes" class="form-agri" rows="4" maxlength="1000" placeholder="Optional additional instructions for the customer."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Return</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="rejectReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('vendor.returns.reject', $return->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Return Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Reason</label>
                        <textarea name="rejection_reason" class="form-agri" rows="4" required maxlength="1000" placeholder="Explain clearly why this return is being rejected."></textarea>
                    </div>
                    <div>
                        <label class="form-label text-muted small">Notes (optional)</label>
                        <textarea name="response_notes" class="form-agri" rows="3" maxlength="1000" placeholder="Optional internal or customer-facing note."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Return</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
