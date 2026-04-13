@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.forum.categories.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Forum</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.forum.categories.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Categories</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Forum Category</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Update category details used across forum discussions.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="card-agri mb-4" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 20px; color: #991b1b; font-weight: 700;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card-agri" style="padding: 28px;">
                <form method="POST" action="{{ route('admin.forum.categories.update', $category->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Name</label>
                            <input type="text" name="name" class="form-agri" value="{{ old('name', $category->name) }}" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Slug</label>
                            <input type="text" name="slug" class="form-agri" value="{{ old('slug', $category->slug) }}" placeholder="Auto-generated from name" disabled>
                        </div>
                        <div class="col-12">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Description</label>
                            <textarea name="description" class="form-agri" rows="4" maxlength="500" placeholder="Optional description">{{ old('description', $category->description) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Sort Order</label>
                            <input type="number" name="sort_order" class="form-agri" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label style="display: flex; align-items: center; gap: 10px; margin: 0; font-weight: 700; color: var(--agri-text-heading);">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                Active
                            </label>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div style="width: 100%; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; padding-bottom: 6px;">
                                Thread count: {{ $category->threads_count }}
                            </div>
                        </div>
                        <div class="col-12">
                            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;">
                                <a href="{{ route('admin.forum.categories.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Cancel</a>
                                <button type="submit" class="btn-agri btn-agri-primary" style="min-width: 160px;">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection