@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.returns.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Returns & Refunds</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Request Details</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Return Request #{{ $return->id }}</h1>
            
            @php
                $statusMap = [
                    'pending' => ['bg' => '#fffbeb', 'color' => '#d97706', 'icon' => 'clock'],
                    'approved' => ['bg' => 'var(--agri-primary-light)', 'color' => 'var(--agri-primary)', 'icon' => 'check-circle'],
                    'rejected' => ['bg' => '#FEF2F2', 'color' => 'var(--agri-error)', 'icon' => 'times-circle'],
                    'refunded' => ['bg' => 'var(--agri-success-light)', 'color' => 'var(--agri-success)', 'icon' => 'wallet']
                ];
                $st = $statusMap[$return->status] ?? ['bg' => 'var(--agri-bg)', 'color' => 'var(--agri-text-muted)', 'icon' => 'info-circle'];
            @endphp
            <span style="background: {{ $st['bg'] }}; color: {{ $st['color'] }}; padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-{{ $st['icon'] }}"></i> {{ $return->status }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Main Information Card --}}
            <div class="card-agri" style="padding: 32px; background: white; margin-bottom: 24px;">
                <h5 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Customer & Order Context</h5>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); padding: 20px; border-radius: 16px;">
                            <label style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px;">Customer Details</label>
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; color: var(--agri-primary); border: 1px solid var(--agri-border); font-size: 18px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">{{ $return->user->name ?? 'External User' }}</div>
                                    <div style="font-size: 12px; color: var(--agri-text-muted); font-weight: 600;">{{ $return->user->email ?? 'No email provided' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); padding: 20px; border-radius: 16px;">
                            <label style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px;">Source Order</label>
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; color: var(--agri-primary); border: 1px solid var(--agri-border); font-size: 18px;">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 15px;">Order #{{ $return->order_id }}</div>
                                    <div style="font-size: 12px; color: var(--agri-text-muted); font-weight: 600;">Placed on {{ $return->order->created_at->format('M d, Y') }}</div>
                                </div>
                                <a href="{{ route('admin.orders.show', $return->order_id) }}" class="btn-agri" style="margin-left: auto; padding: 6px 12px; font-size: 11px; font-weight: 800; text-decoration: none; background: white; color: var(--agri-primary);">VIEW ORDER</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 32px; padding-top: 32px; border-top: 1px solid var(--agri-border);">
                    <h6 style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Reason for Return</h6>
                    <div style="font-size: 15px; font-weight: 600; color: var(--agri-text-main); line-height: 1.6; background: #fffbeb; padding: 16px; border-radius: 12px; border-left: 4px solid #d97706;">
                        {{ $return->reason->reason ?? $return->reason_text ?? 'The customer did not specify a primary reason.' }}
                    </div>
                    
                    @if($return->customer_notes)
                    <div style="margin-top: 24px;">
                        <h6 style="font-size: 14px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Customer Comments</h6>
                        <div style="font-size: 14px; color: var(--agri-text-main); line-height: 1.6; background: var(--agri-bg); padding: 16px; border-radius: 12px; border: 1px solid var(--agri-border);">
                            "{{ $return->customer_notes }}"
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action Center --}}
            @if($return->status === 'pending')
            <div class="card-agri" style="padding: 32px; background: white; border: 1px solid var(--agri-primary); box-shadow: 0 10px 40px rgba(71, 142, 60, 0.08);">
                <h5 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-gavel"></i> Administrative Decision Center
                </h5>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div style="padding: 24px; border-radius: 20px; border: 1px solid var(--agri-success); background: var(--agri-success-light);">
                            <h6 style="font-weight: 800; color: var(--agri-success); margin-bottom: 16px;">Approve Return</h6>
                            <form action="{{ route('admin.returns.approve', $return->id) }}" method="POST">
                                @csrf
                                <textarea name="admin_note" class="form-agri" rows="2" placeholder="Send a message to the customer (item received, etc.)..." style="background: white; margin-bottom: 16px;"></textarea>
                                <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; height: 48px; font-weight: 700;">
                                    <i class="fas fa-check-circle"></i> Confirm Approval
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="padding: 24px; border-radius: 20px; border: 1px solid var(--agri-error); background: #FEF2F2;">
                            <h6 style="font-weight: 800; color: var(--agri-error); margin-bottom: 16px;">Decline Return</h6>
                            <form action="{{ route('admin.returns.reject', $return->id) }}" method="POST">
                                @csrf
                                <textarea name="admin_note" class="form-agri" rows="2" placeholder="Provide a reason for declining the request..." required style="background: white; margin-bottom: 16px;"></textarea>
                                <button type="submit" class="btn-agri" style="width: 100%; height: 48px; background: var(--agri-error); color: white; border: none; font-weight: 700; border-radius: 12px;">
                                    <i class="fas fa-times-circle"></i> Decline Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($return->status === 'approved' && !$return->refund)
            <div class="card-agri" style="padding: 32px; background: white; border: 1px solid #166534; box-shadow: 0 10px 40px rgba(22, 101, 52, 0.08);">
                <h5 style="font-size: 18px; font-weight: 700; color: #166534; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-undo-alt"></i> Financial Reversal (Refund)
                </h5>
                @if($canRefund)
                    <form action="{{ route('admin.returns.refund', $return->id) }}" method="POST">
                        @csrf
                        <div class="row g-4 align-items-end">
                            <div class="col-md-5">
                                <label class="agri-label">REFUNDABLE AMOUNT ({{ config('plantix.currency_symbol') }})</label>
                                <div style="position: relative;">
                                    <input type="number" name="amount" step="0.01" min="0" class="form-agri" value="{{ $return->order->grand_total }}" required style="padding-left: 20px; font-size: 18px; font-weight: 800; height: 52px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="agri-label">DISBURSEMENT METHOD</label>
                                <select name="method" class="form-agri" style="height: 52px; font-weight: 700;">
                                    <option value="original_payment">Original Payment Method</option>
                                    <option value="bank_transfer">Direct Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; height: 52px; font-weight: 700; font-size: 15px;">
                                    Execute Refund
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div style="background: #f8fafc; border: 1px dashed var(--agri-border); border-radius: 16px; padding: 18px 20px; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">
                        Refund disabled for this order because one or more items are not refundable.
                    </div>
                @endif
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Refund Summary Sidebar --}}
            @if($return->refund)
            <div class="card-agri" style="padding: 24px; background: white; border: 1px solid var(--agri-success-light);">
                <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 20px;">Execution Summary</h5>
                <div style="background: var(--agri-success-light); padding: 20px; border-radius: 16px; text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 11px; font-weight: 700; color: var(--agri-success); text-transform: uppercase;">Refunded Amount</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--agri-success); margin: 4px 0;">{{ config('plantix.currency_symbol') }}{{ number_format($return->refund->amount, 2) }}</div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">Payment Channel</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ ucfirst($return->refund->method ?? 'Wallet') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">Transaction ID</span>
                        <span style="font-size: 11px; font-weight: 700; color: var(--agri-text-heading); background: var(--agri-bg); padding: 2px 8px; border-radius: 4px;">#TXN-{{ $return->refund->id }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">Settled Date</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ $return->refund->processed_at?->format('M d, Y') ?? 'Confirmed' }}</span>
                    </div>
                </div>
            </div>
            @else
            <div class="card-agri" style="padding: 24px; background: var(--agri-bg); border: 1px dashed var(--agri-border); text-align: center;">
                <div style="font-size: 32px; color: var(--agri-border); margin-bottom: 16px;"><i class="fas fa-hand-holding-usd"></i></div>
                <h6 style="font-weight: 700; color: var(--agri-text-muted);">Financial Settlement Pending</h6>
                <p style="font-size: 12px; color: var(--agri-text-muted); margin: 8px 0 0;">Refund data will be populated here once the administrative decision is executed.</p>
            </div>
            @endif

            <div style="margin-top: 24px;">
                <a href="{{ route('admin.returns.index') }}" class="btn-agri btn-agri-outline" style="width: 100%; height: 48px; text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 700; gap: 8px;">
                    <i class="fas fa-chevron-left"></i> Return to Ledger
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
