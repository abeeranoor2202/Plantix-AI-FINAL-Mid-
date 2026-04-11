@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Attributes</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ trans('lang.item_attribute_plural') }}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage product attributes.</p>
        </div>
        <a href="{{ route('admin.attributes.create') }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i>
            {{ trans('lang.attribute_create') }}
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">{{ trans('lang.attribute_table') }}</h4>
            <div class="input-group" style="width: 320px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                    <i class="fas fa-search" style="color: var(--agri-text-muted); font-size: 14px;"></i>
                </span>
                <input type="text" id="search-input" class="form-agri border-start-0" placeholder="Search attributes..." style="margin-bottom: 0; border-radius: 0 10px 10px 0; height: 42px;">
            </div>
        </div>

        <div class="table-responsive">
            <table id="attributeTable" class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{ trans('lang.attribute_name') }}</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">{{ trans('lang.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($attributes as $attribute)
                    <tr>
                        <td class="px-4 py-3">
                            <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $attribute->title }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.attributes.edit', $attribute->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('admin.attributes.destroy', $attribute->id) }}" class="d-inline" onsubmit="return confirm('Delete this attribute?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center py-5" style="color: var(--agri-text-muted);">No attributes found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    $('#search-input').on('keyup', function () {
        var val = $(this).val().toLowerCase();
        $('#attributeTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });
});
</script>
@endsection
