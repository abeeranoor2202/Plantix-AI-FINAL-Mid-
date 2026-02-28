@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Ecosystem</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Stakeholder Management</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                @if(request()->is('vendors/approved'))
                    Verified Agriculture Experts
                @elseif(request()->is('vendors/pending'))
                    Pending Verification Queue
                @else
                    Service Partner Ecosystem
                @endif
            </h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Orchestrate and verify the credentials of agriculture consultants and vendors.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <div style="background: white; padding: 10px 20px; border-radius: 14px; border: 1px solid var(--agri-border); font-size: 13px; font-weight: 800; color: var(--agri-primary); display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <i class="fas fa-certificate"></i>
                QUALITY ASSURED PARTNERS
            </div>
        </div>
    </div>

    {{-- Strategy Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div style="padding: 24px 32px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center; background: white;">
            <div style="display: flex; gap: 8px; background: var(--agri-bg); padding: 6px; border-radius: 16px; border: 1px solid var(--agri-border);">
                <a href="{{ route('admin.vendors') }}" class="btn-agri {{ !request()->filled('status') && !request()->filled('approval') ? 'btn-agri-primary' : '' }}" style="padding: 8px 24px; font-size: 12px; text-decoration: none; font-weight: 800; border-radius: 12px; border: none; background: {{ !request()->filled('status') && !request()->filled('approval') ? 'var(--agri-primary)' : 'transparent' }}; color: {{ !request()->filled('status') && !request()->filled('approval') ? 'white' : 'var(--agri-text-muted)' }};">
                    All Vendors
                </a>
                <a href="{{ route('admin.vendors', ['approval' => 'approved']) }}" class="btn-agri {{ request()->query('approval') === 'approved' ? 'btn-agri-primary' : '' }}" style="padding: 8px 24px; font-size: 12px; text-decoration: none; font-weight: 800; border-radius: 12px; border: none; background: {{ request()->query('approval') === 'approved' ? 'var(--agri-primary)' : 'transparent' }}; color: {{ request()->query('approval') === 'approved' ? 'white' : 'var(--agri-text-muted)' }};">
                    Approved
                </a>
                <a href="{{ route('admin.vendors', ['approval' => 'pending']) }}" class="btn-agri {{ request()->query('approval') === 'pending' ? 'btn-agri-primary' : '' }}" style="padding: 8px 24px; font-size: 12px; text-decoration: none; font-weight: 800; border-radius: 12px; border: none; background: {{ request()->query('approval') === 'pending' ? 'var(--agri-primary)' : 'transparent' }}; color: {{ request()->query('approval') === 'pending' ? 'white' : 'var(--agri-text-muted)' }};">
                    Pending
                </a>
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--agri-primary); font-size: 14px; opacity: 0.7;"></i>
                    <form method="GET" action="{{ route('admin.vendors') }}" style="display: inline;">
                        <input type="text" name="search" placeholder="Search vendors..." class="form-agri" value="{{ request('search') }}" style="padding-left: 44px; width: 300px; height: 44px; font-size: 14px; font-weight: 600;">
                    </form>
                </div>

                @if(in_array('vendors', json_decode(session('admin_permissions', '[]'), true)))
                    <button id="deleteAll" class="btn-agri" style="color: var(--agri-error); font-size: 13px; font-weight: 800; border: none; border-radius: 12px; padding: 12px 20px; background: #FEF2F2; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-trash-alt"></i> Bulk Delete
                    </button>
                @endif
            </div>
        </div>

        <div id="data-table_processing" class="dataTables_processing" style="display: none; background: rgba(255,255,255,0.95); position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; z-index: 100;">
            <div style="text-align: center;">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
                <div style="margin-top: 16px; font-weight: 800; color: var(--agri-primary); letter-spacing: 1px;">SYNCING REGISTRY...</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle; width: 100%;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Vendor Name</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Owner</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Phone</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Approval</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-center">Status</th>
                        <th style="padding: 20px 32px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr style="border-bottom: 1px solid var(--agri-border);">
                            <td style="padding: 16px 32px;">
                                <div style="font-weight: 700; color: var(--agri-primary-dark); font-size: 14px;">
                                    {{ $vendor->title }}
                                </div>
                                <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 4px;">
                                    {{ \Illuminate\Support\Str::limit($vendor->description, 50) }}
                                </div>
                            </td>
                            <td style="padding: 16px 32px; font-size: 14px;">
                                {{ $vendor->author?->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 16px 32px; font-size: 14px;">
                                {{ $vendor->phone }}
                            </td>
                            <td style="padding: 16px 32px; text-align: center;">
                                <span style="display: inline-block; padding: 6px 12px; border-radius: 8px; background: {{ $vendor->is_approved ? '#d4edda' : '#fff3cd' }}; color: {{ $vendor->is_approved ? '#155724' : '#856404' }}; font-weight: 600; font-size: 12px;">
                                    {{ $vendor->is_approved ? '✓ Approved' : '⏳ Pending' }}
                                </span>
                            </td>
                            <td style="padding: 16px 32px; text-align: center;">
                                <span style="display: inline-block; padding: 6px 12px; border-radius: 8px; background: {{ $vendor->is_active ? '#d4edda' : '#f8d7da' }}; color: {{ $vendor->is_active ? '#155724' : '#721c24' }}; font-weight: 600; font-size: 12px;">
                                    {{ $vendor->is_active ? '✓ Active' : '✗ Inactive' }}
                                </span>
                            </td>
                            <td style="padding: 16px 32px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('admin.vendors.view', $vendor->id) }}" class="btn btn-sm btn-info" style="font-size: 12px; padding: 6px 12px;">
                                        View
                                    </a>
                                    <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 6px 12px;">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: var(--agri-text-muted); font-weight: 700;">
                                No vendors found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($vendors->hasPages())
            <div style="padding: 24px 32px; border-top: 1px solid var(--agri-border); display: flex; justify-content: between; align-items: center;">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .form-check-input:checked { background-color: var(--agri-primary); border-color: var(--agri-primary); }
</style>
@endsection