@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.forum.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Forum</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Categories</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Forum Categories</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Create and manage category taxonomy for moderated discussions.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 12px; padding: 12px 20px; color: #166534; font-weight: 700;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="card-agri mb-4" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 20px; color: #991b1b; font-weight: 700;">
            {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-agri" style="padding: 24px;">
                <h4 style="font-size: 17px; font-weight: 700; color: var(--agri-text-heading); margin: 0 0 16px 0;">Add Category</h4>
                <form method="POST" action="{{ route('admin.forum.categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Name</label>
                        <input type="text" name="name" class="form-agri" required maxlength="100" value="{{ old('name') }}" placeholder="e.g. Pest Control">
                        @error('name')
                            <div style="color: #dc2626; font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Description</label>
                        <textarea name="description" class="form-agri" rows="3" placeholder="Optional description">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Slug</label>
                        <input type="text" name="slug" class="form-agri" value="{{ old('slug') }}" placeholder="Auto-generated if empty">
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; height: 42px;">Create Category</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Category List</h4>
                    <span class="badge rounded-pill bg-success">{{ $categories->count() }} Categories</span>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">#</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Name</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Slug</th>
                                <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Threads</th>
                                <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                                <tr>
                                    <td class="px-4 py-3">{{ $cat->id }}</td>
                                    <td class="px-4 py-3">{{ $cat->name }}</td>
                                    <td class="px-4 py-3" style="font-family: monospace; color: var(--agri-text-muted);">{{ $cat->slug }}</td>
                                    <td class="px-4 py-3"><strong>{{ $cat->threads_count ?? 0 }}</strong></td>
                                    <td class="px-4 py-3">
                                        <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                            <a href="{{ route('admin.forum.categories.edit', $cat->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: var(--agri-primary); border-radius: 999px;" title="Edit"><i class="fas fa-pen"></i></a>
                                            <form method="POST" action="{{ route('admin.forum.categories.destroy', $cat->id) }}" class="d-inline" onsubmit="return confirm('Delete category?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5" style="color: var(--agri-text-muted);">No categories found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
