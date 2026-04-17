@extends('layouts.frontend')

@section('title', 'Start a New Discussion | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(35, 77, 32, 0.08), rgba(35, 77, 32, 0.02));">
        <div class="container-agri">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Start a New Discussion</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('forum') }}" class="text-success text-decoration-none">Forum</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">New Thread</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div id="forum-new-page" class="py-5" style="background: var(--agri-bg); min-height: 70vh;">
        <div class="container-agri">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="card-agri" style="padding: 32px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                            <div style="width: 36px; height: 36px; background: var(--panel-primary-soft); color: var(--panel-primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-pen"></i></div>
                            <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Create a Thread</h6>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger mb-4" style="border-radius: 12px; font-weight: 600; padding: 16px;">
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('forum.store') }}">
                            @csrf
                            <div style="margin-bottom: 20px;">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Discussion Title <span style="color: #DC2626;">*</span></label>
                                <input name="title" class="form-agri" placeholder="e.g. How to prevent late blight in potatoes?" value="{{ old('title') }}" required>
                                @error('title')<div style="color: #DC2626; font-size: 12px; margin-top: 6px; font-weight: 600;"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Category</label>
                                <select name="forum_category_id" class="form-agri text-dark">
                                    <option value="" disabled selected>-- Select a relevant category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('forum_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('forum_category_id')<div style="color: #DC2626; font-size: 12px; margin-top: 6px; font-weight: 600;"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                            </div>
                            
                            <div style="margin-bottom: 32px;">
                                <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Detailed Description <span style="color: #DC2626;">*</span></label>
                                <textarea name="body" class="form-agri" rows="8" placeholder="Please describe your question or topic in detail. Include any relevant context like crop age, symptoms, or what you've already tried." required>{{ old('body') }}</textarea>
                                @error('body')<div style="color: #DC2626; font-size: 12px; margin-top: 6px; font-weight: 600;"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid var(--agri-border);">
                                <a href="{{ route('forum') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; font-weight: 600; padding: 12px 24px;">Cancel</a>
                                <button type="submit" class="btn-agri btn-agri-primary" style="font-weight: 700; padding: 12px 24px;">
                                    Post Discussion <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <p class="text-muted small"><i class="fas fa-shield-alt text-success me-1"></i> Please follow our community guidelines. Be respectful and helpful to fellow farmers.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
