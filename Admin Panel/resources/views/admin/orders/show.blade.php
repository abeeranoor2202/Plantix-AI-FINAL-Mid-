@extends('layouts.app')

@section('title', 'Transaction Telemetry: ' . $order->order_number)

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.orders.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Fulfillment Hub</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Transaction Telemetry</span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Order <span style="color: var(--agri-primary);">#{{ $order->order_number }}</span></h1>
                
                @php
                    $bc = [
                        'pending'         => ['#B45309', '#FEF3C7'],
                        'accepted'        => ['#1D4ED8', '#DBEAFE'],
                        'preparing'       => ['#6D28D9', '#EDE9FE'],
                        'ready'           => ['#4338CA', '#E0E7FF'],
                        'driver_assigned' => ['#374151', '#E5E7EB'],
                        'picked_up'       => ['#047857', '#D1FAE5'],
                        'delivered'       => ['#047857', '#D1FAE5'],
                        'rejected'        => ['#B91C1C', '#FEE2E2'],
                        'cancelled'       => ['#B91C1C', '#FEE2E2'],
                    ];
                    $currentStatus = $bc[$order->status] ?? ['#4B5563', '#F3F4F6'];
                @endphp
                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $currentStatus[0] }}; background: {{ $currentStatus[1] }}; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 900; border: 1px solid {{ $currentStatus[0] }}40; text-transform: uppercase; margin-top: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $currentStatus[0] }}; box-shadow: 0 0 0 2px {{ $currentStatus[0] }}30;"></span>
                    {{ str_replace('_', ' ', $order->status) }}
                </div>
            </div>
            <p style="color: var(--agri-text-muted); margin: 8px 0 0 0;">Comprehensive telemetry and operational control for this specific physical asset transfer.</p>
        </div>
        <div style="display: flex; gap: 16px;">
            <a href="{{ route('admin.orders.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700; padding: 12px 24px;">
                <i class="fas fa-arrow-left"></i> Return to Ledger
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3 mb-4" style="border-radius: 16px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="opacity: 0.5; filter: invert(1);"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Left column: items + status history --}}
        <div class="col-lg-8">

            {{-- Order Items --}}
            <div class="card-agri mb-4" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Asset Manifest ({{ $order->items->count() }})</h5>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none; border-top-left-radius: 12px; border-bottom-left-radius: 12px;">Physical Asset Designation</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Units</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-end">Base Value</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none; border-top-right-radius: 12px; border-bottom-right-radius: 12px;" class="text-end">Net Exchange</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 20px 24px;">
                                    @if($item->product)
                                        <a href="{{ route('admin.products.show', $item->product->id) }}" style="text-decoration: none; font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">
                                            {{ $item->product->name }}
                                        </a>
                                    @else
                                        <span style="font-weight: 700; color: var(--agri-text-muted); font-size: 14px; text-decoration: line-through;">{{ $item->product_name ?? 'Asset Class Terminated' }}</span>
                                    @endif
                                </td>
                                <td style="padding: 20px 24px;" class="text-center">
                                    <div style="background: var(--agri-bg); color: var(--agri-primary-dark); padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; display: inline-block;">
                                        ×{{ $item->quantity }}
                                    </div>
                                </td>
                                <td style="padding: 20px 24px; font-weight: 600; color: var(--agri-text-muted);" class="text-end">
                                    {{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}
                                </td>
                                <td style="padding: 20px 24px; font-weight: 800; color: var(--agri-text-heading); font-size: 15px;" class="text-end">
                                    {{ config('plantix.currency_symbol', 'PKR') }} {{ number_format(($item->unit_price ?? $item->price ?? 0) * $item->quantity, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background: transparent;">
                            <tr>
                                <td colspan="3" class="text-end" style="padding: 24px 24px 12px 24px; font-weight: 700; color: var(--agri-text-muted); font-size: 13px; border: none;">Subtotal Core Value</td>
                                <td class="text-end" style="padding: 24px 24px 12px 24px; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; border: none;">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end" style="padding: 8px 24px; font-weight: 700; color: var(--agri-text-muted); font-size: 13px; border: none;">Logistics Deployment Fee</td>
                                <td class="text-end" style="padding: 8px 24px; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; border: none;">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($order->delivery_fee, 2) }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end" style="padding: 8px 24px; font-weight: 700; color: var(--agri-success); font-size: 13px; border: none;">Platform Incentive (Discount)</td>
                                <td class="text-end" style="padding: 8px 24px; font-weight: 800; color: var(--agri-success); font-size: 15px; border: none;">− {{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($order->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->tax_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end" style="padding: 8px 24px; font-weight: 700; color: var(--agri-text-muted); font-size: 13px; border: none;">Regulatory Levy (Tax)</td>
                                <td class="text-end" style="padding: 8px 24px; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; border: none;">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end" style="padding: 20px 24px; font-weight: 900; color: var(--agri-primary-dark); font-size: 16px; border-top: 2px dashed var(--agri-border); border-bottom: none;">GROSS SETTLEMENT VALUE</td>
                                <td class="text-end" style="padding: 20px 24px; font-weight: 900; color: var(--agri-primary-dark); font-size: 20px; border-top: 2px dashed var(--agri-border); border-bottom: none; letter-spacing: -0.5px;">{{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Status History --}}
            <div class="card-agri mb-4" style="padding: 32px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-secondary-light); color: var(--agri-primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">State Progression Telemetry</h5>
                </div>
                
                <div style="position: relative; padding-left: 20px; margin-top: 20px;">
                    <div style="position: absolute; left: 26px; top: 0; bottom: 0; width: 2px; background: var(--agri-border); z-index: 1;"></div>
                    
                    @forelse($order->statusHistory as $h)
                    <div style="position: relative; margin-bottom: 24px; padding-left: 32px; z-index: 2;">
                        <div style="position: absolute; left: 2px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--agri-primary); border: 2px solid white; box-shadow: 0 0 0 3px var(--agri-primary-light);"></div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span style="background: var(--agri-bg); color: var(--agri-text-heading); font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; border: 1px solid var(--agri-border);">
                                    {{ str_replace('_', ' ', $h->status) }}
                                </span>
                                @if($h->notes)
                                    <p style="margin: 8px 0 0 0; font-size: 13px; color: var(--agri-text-muted); font-weight: 500; background: #F9FAFB; padding: 8px 12px; border-radius: 8px; border-left: 3px solid var(--agri-primary);">{{ $h->notes }}</p>
                                @endif
                            </div>
                            <div style="text-align: right; color: var(--agri-text-muted); font-size: 11px; font-weight: 600;">
                                <div style="color: var(--agri-primary);">{{ $h->created_at->format('M d, Y • H:i') }}</div>
                                @if($h->changedBy)
                                    <div style="margin-top: 2px; opacity: 0.7;">Operated by: {{ $h->changedBy->name }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--agri-text-muted); font-style: italic; padding: 20px;">No telemetry alterations recorded.</div>
                    @endforelse
                </div>
            </div>

            {{-- Return / Refund (if any) --}}
            @if($order->returnRequest)
            <div class="card-agri mb-4" style="padding: 32px; background: #FFFBEB; border: 1px solid #FDE68A; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; border-bottom: 1px solid #FDE68A; padding-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; background: #FEF3C7; color: #D97706; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <h5 style="margin: 0; font-weight: 800; color: #92400E; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Asset Reversal Protocol</h5>
                    </div>
                    <span style="background: #FEF3C7; color: #B45309; padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase; border: 1px solid #FCD34D;">
                        {{ $order->returnRequest->status }}
                    </span>
                </div>
                <div style="font-size: 14px; color: #92400E;">
                    <div style="display: flex; margin-bottom: 12px;">
                        <div style="width: 140px; font-weight: 800; text-transform: uppercase; font-size: 11px; opacity: 0.7; padding-top: 2px;">Validation Vector:</div>
                        <div style="flex: 1; font-weight: 600;">{{ $order->returnRequest->reason->reason ?? $order->returnRequest->reason_text ?? 'DATA CORRUPTED' }}</div>
                    </div>
                    @if($order->returnRequest->description)
                        <div style="display: flex; margin-bottom: 12px;">
                            <div style="width: 140px; font-weight: 800; text-transform: uppercase; font-size: 11px; opacity: 0.7; padding-top: 2px;">User Context:</div>
                            <div style="flex: 1; background: white; padding: 12px; border-radius: 8px; border: 1px solid #FDE68A; font-weight: 500;">{{ $order->returnRequest->description }}</div>
                        </div>
                    @endif
                    @if($order->returnRequest->admin_notes)
                        <div style="display: flex; margin-bottom: 12px;">
                            <div style="width: 140px; font-weight: 800; text-transform: uppercase; font-size: 11px; opacity: 0.7; padding-top: 2px;">Command Notes:</div>
                            <div style="flex: 1; font-weight: 600;">{{ $order->returnRequest->admin_notes }}</div>
                        </div>
                    @endif
                    
                    @if($order->refund)
                        <div style="margin-top: 24px; background: white; border: 1px solid #A7F3D0; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 16px;">
                            <div style="width: 40px; height: 40px; background: #D1FAE5; border-radius: 50%; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: #065F46; font-size: 14px;">Capital Reallocation Successful</div>
                                <div style="font-size: 12px; color: #047857; font-weight: 500; margin-top: 2px;">
                                    {{ config('plantix.currency_symbol', 'PKR') }} {{ number_format($order->refund->amount, 2) }} reimbursed via {{ strtoupper($order->refund->method) }} on {{ $order->refund->processed_at?->format('M d, Y') ?? 'UNKNOWN DATE' }}.
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div style="margin-top: 24px; text-align: right;">
                        <a href="{{ route('admin.returns.show', $order->returnRequest->id) }}" class="btn-agri" style="padding: 10px 20px; background: white; color: #D97706; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 800; border: 1px solid #FCD34D;">
                            ACCESS FULL VECTOR <i class="fas fa-external-link-alt" style="margin-left: 6px;"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right column: details + actions --}}
        <div class="col-lg-4">

            {{-- Update Status --}}
            @if(!in_array($order->status, ['delivered','cancelled','rejected']))
            <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                    <div style="width: 32px; height: 32px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Override Logistics State</h5>
                </div>
                <div>
                    <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="agri-label">New Designation Vector</label>
                            <select name="status" class="form-agri" required style="font-weight: 700; font-size: 13px;">
                                @foreach(['pending','accepted','preparing','ready','picked_up','delivered','rejected','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                                        {{ strtoupper(str_replace('_', ' ', $s)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="agri-label">Override Protocol Remarks</label>
                            <textarea name="notes" class="form-agri" rows="2" placeholder="Mandatory context for state alteration..." style="font-size: 13px;"></textarea>
                        </div>
                        <button type="submit" class="btn-agri btn-agri-primary w-100" style="padding: 14px; font-weight: 800; border-radius: 12px; font-size: 14px; letter-spacing: 0.5px;">
                            EXECUTE OVERRIDE
                        </button>
                    </form>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 32px; height: 32px; background: #EEF2FF; color: #4F46E5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Transaction Metadata</h5>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Ledger Reference</span>
                        <span style="font-size: 13px; font-weight: 800; color: var(--agri-text-heading);">{{ $order->order_number }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Originating Account</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-primary);">{{ $order->user->name ?? 'VOID' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Fulfillment Partner</span>
                        <span style="font-size: 13px; font-weight: 700; color: var(--agri-secondary);">{{ $order->vendor->name ?? 'SYSTEM-DIRECT' }}</span>
                    </div>
                    <div style="height: 1px; background: var(--agri-border); margin: 4px 0;"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Capital Channel</span>
                        <span style="font-size: 12px; font-weight: 800; background: var(--agri-bg); padding: 4px 10px; border-radius: 6px; text-transform: uppercase;">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Settlement Status</span>
                        @php 
                            $payClass = ['paid'=>'#059669','pending'=>'#B45309','failed'=>'#DC2626','refunded'=>'#4B5563']; 
                            $payBg = ['paid'=>'#D1FAE5','pending'=>'#FEF3C7','failed'=>'#FEE2E2','refunded'=>'#F3F4F6']; 
                            $status = $order->payment_status;
                        @endphp
                        <span style="font-size: 10px; font-weight: 900; background: {{ $payBg[$status] ?? '#F3F4F6' }}; color: {{ $payClass[$status] ?? '#4B5563' }}; padding: 4px 10px; border-radius: 100px; text-transform: uppercase;">
                            {{ $status }}
                        </span>
                    </div>
                    @if($order->coupon)
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Incentive Hash</span>
                        <span style="font-size: 12px; font-weight: 800; color: var(--agri-success); border: 1px dashed var(--agri-success); padding: 2px 8px; border-radius: 4px;">{{ $order->coupon->code }}</span>
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">Timestamp</span>
                        <span style="font-size: 12px; font-weight: 600; color: var(--agri-text-heading);">{{ $order->created_at->format('M d, Y • H:i:s') }}</span>
                    </div>
                </div>
            </div>

            {{-- Delivery Address --}}
            @if($order->delivery_address)
            <div class="card-agri mb-4" style="padding: 24px; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                    <div style="width: 32px; height: 32px; background: #ECFDF5; color: #059669; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Geospatial Target</h5>
                </div>
                <div style="background: var(--agri-bg); padding: 16px; border-radius: 12px; font-size: 13px; color: var(--agri-text-heading); font-weight: 500; line-height: 1.5; border: 1px solid var(--agri-border);">
                    {{ $order->delivery_address }}
                </div>
                @if($order->delivery_lat && $order->delivery_lng)
                    <div style="margin-top: 16px;">
                        <a href="https://maps.google.com/?q={{ $order->delivery_lat }},{{ $order->delivery_lng }}"
                           target="_blank" class="btn-agri w-100" style="padding: 12px; background: white; color: var(--agri-primary); border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; border: 1px solid var(--agri-primary)40; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-location-arrow"></i> LOCK SATELLITE VECTOR
                        </a>
                    </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: block; }
    .form-agri:focus { border-color: var(--agri-primary) !important; background: white !important; }
</style>
@endsection
