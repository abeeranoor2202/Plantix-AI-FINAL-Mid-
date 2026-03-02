@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.forum.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    Forum
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Categories</span>
            </div>
            <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;"><i class="fa fa-tags text-success me-2"></i> Forum Categories</h1>
        </div>
    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert mb-4" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-times-circle" style="font-size: 18px;"></i> {{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">

            {{-- Create Category Form --}}
            <div class="col-lg-4">
                <div class="card-agri" style="padding: 28px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                        <div style="width: 36px; height: 36px; background: #D1FAE5; color: #059669; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-plus"></i></div>
                        <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Add Category</h6>
                    </div>
                    <form method="POST" action="{{ route('admin.forum.categories.store') }}">
                        @csrf
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Name <span style="color: #DC2626;">*</span></label>
                            <input type="text" name="name" class="form-agri" placeholder="e.g. Pest Control" required maxlength="100" value="{{ old('name') }}">
                            @error('name')<div style="color: #DC2626; font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>@enderror
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Description</label>
                            <textarea name="description" class="form-agri" rows="3" placeholder="Optional description…">{{ old('description') }}</textarea>
                        </div>
                        <div style="margin-bottom: 24px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Slug (auto-generated if blank)</label>
                            <input type="text" name="slug" class="form-agri" placeholder="pest-control" value="{{ old('slug') }}">
                        </div>
                        <button type="submit" class="btn-agri btn-agri-primary w-100" style="justify-content: center; font-weight: 700; padding: 14px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa fa-plus"></i> Create Category
                        </button>
                    </form>
                </div>
            </div>

            {{-- Categories List --}}
            <div class="col-lg-8">
                <div class="card-agri" style="padding: 0; overflow: hidden;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-list"></i></div>
                            <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">All Categories</h6>
                        </div>
                        <span style="background: #D1FAE5; color: #065F46; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $categories->count() }} Categories</span>
                    </div>
                    <div class="table-responsive">
                        @if($categories->isEmpty())
                            <div style="padding: 60px 24px; text-align: center; color: var(--agri-text-muted);">
                                <i class="fa fa-folder-open" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px;"></i>
                                <p style="margin: 0; font-weight: 600; color: var(--agri-text-heading);">No categories yet.</p>
                                <p style="margin: 4px 0 0 0; font-size: 14px;">Use the form to create your first forum category.</p>
                            </div>
                        @else
                            <table class="table mb-0" style="vertical-align: middle;">
                                <thead style="background: var(--agri-bg);">
                                    <tr>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; width: 60px;">#</th>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Name</th>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Slug</th>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Threads</th>
                                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: end;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $cat)
                                    <tr style="border-bottom: 1px solid var(--agri-border);">
                                        <td style="padding: 18px 24px; font-size: 13px; font-weight: 600; color: var(--agri-text-muted);">{{ $cat->id }}</td>
                                        <td style="padding: 18px 24px;">
                                            <form method="POST" action="{{ route('admin.forum.categories.update', $cat->id) }}"
                                                  style="display: flex; gap: 8px; align-items: center;" id="edit-cat-{{ $cat->id }}">
                                                @csrf @method('PUT')
                                                <input type="text" name="name" class="form-agri"
                                                       value="{{ $cat->name }}" style="max-width:200px; padding: 8px 14px; font-size: 13px;">
                                            </form>
                                        </td>
                                        <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-muted);"><code>{{ $cat->slug }}</code></td>
                                        <td style="padding: 18px 24px; text-align: center; font-size: 14px; font-weight: 800; color: var(--agri-primary-dark);">{{ $cat->threads_count ?? 0 }}</td>
                                        <td style="padding: 18px 24px; text-align: end;">
                                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                                <button type="submit" form="edit-cat-{{ $cat->id }}" class="btn-agri btn-agri-primary" style="padding: 8px 12px; font-size: 12px; font-weight: 600;">
                                                    <i class="fa fa-save"></i> Save
                                                </button>
                                                <form method="POST" action="{{ route('admin.forum.categories.destroy', $cat->id) }}" class="d-inline" onsubmit="return confirm('Delete category?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-agri" style="padding: 8px 12px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; font-weight: 600;">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
