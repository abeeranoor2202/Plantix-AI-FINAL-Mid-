@extends('layouts.frontend')

@section('title', 'Start a New Discussion | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.01));">
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
                    
                    <div class="card-agri p-4 p-md-5 border-0 shadow-sm">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <i class="fas fa-pen-square text-success fs-2 me-3"></i>
                            <div>
                                <h3 class="fw-bold text-dark mb-1">Create a Thread</h3>
                                <p class="text-muted small m-0">Ask questions, share insights, or get help from the community.</p>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger mb-4 py-2 border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger">
                                <ul class="mb-0 small fw-medium">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('forum.store') }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">Discussion Title <span class="text-danger">*</span></label>
                                <input name="title" class="form-agri" placeholder="e.g. How to prevent late blight in potatoes?" value="{{ old('title') }}" required>
                                @error('title')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">Category</label>
                                <select name="forum_category_id" class="form-agri text-dark">
                                    <option value="" disabled selected>-- Select a relevant category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('forum_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('forum_category_id')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                            </div>
                            
                            <div class="mb-5">
                                <label class="form-label fw-bold text-dark small">Detailed Description <span class="text-danger">*</span></label>
                                <textarea name="body" class="form-agri" rows="8" placeholder="Please describe your question or topic in detail. Include any relevant context like crop age, symptoms, or what you've already tried." required>{{ old('body') }}</textarea>
                                @error('body')<div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>@enderror
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <a href="{{ route('forum') }}" class="btn-agri btn-agri-outline px-4 text-dark shadow-sm">Cancel</a>
                                <button type="submit" class="btn-agri btn-agri-primary px-5 shadow-sm">
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
