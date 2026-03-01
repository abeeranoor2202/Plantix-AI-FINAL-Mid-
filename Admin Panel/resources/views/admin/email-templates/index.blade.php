@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px;">

    {{-- Breadcrumb/Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{trans('lang.email_templates')}}</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">System Notifications</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage communication templates sent to farmers and vendors.</p>
    </div>

    {{-- Table Card --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <div style="display: flex; align-items: center; gap: 16px;">
                 <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Email Templates</h4>
                 <div id="data-table_processing" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;"></div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: var(--agri-border);">
                        <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                    </span>
                    <input type="text" id="custom-search-input" class="form-agri border-start-0" placeholder="Search templates..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="emailTemplatesTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.type')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{trans('lang.subject')}}</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;" class="text-end">{{trans('lang.actions')}}</th>
                    </tr>
                </thead>
                <tbody id="emailTemplatesTbody">
                            @forelse($templates as $template)
                            <tr>
                                <td style="font-weight:700;">{{ ucwords(str_replace('_',' ', $template->type)) }}</td>
                                <td>{{ $template->subject ?: '—' }}</td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.email-templates.save', $template->id) }}" class="btn btn-sm btn-outline-success" style="border-radius:8px;font-weight:700;">
                                        <i class="fas fa-edit me-1"></i>{{ trans('lang.edit') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No email templates found.</td></tr>
                            @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white border-top-0 py-4 px-4">
        </div>
    </div>
</div>
@endsection
<style>
    #emailTemplatesTable tbody tr:hover { background-color: rgba(var(--agri-primary-rgb), 0.02); }
    #emailTemplatesTable tbody td { border-bottom: 1px solid var(--agri-border); padding: 16px 24px; font-size: 14px; font-weight: 500;}
</style>

@section('scripts')
<script>
    $(document).ready(function () {

    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#emailTemplatesTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });
    });
</script>
@endsection
