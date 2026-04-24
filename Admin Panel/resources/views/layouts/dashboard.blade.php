<!DOCTYPE html>
<html lang="en">
@include('partials.head')
<body class="panel-unified-ui platform-page-wrap">

@hasSection('header')
    @yield('header')
@else
    @include('partials.header-agri')
@endif

<div class="container-fluid">
    <div class="platform-role-shell">
        <aside class="platform-role-sidebar">
            @include('layouts.customer-menu')
        </aside>
        <main class="platform-role-main">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" id="platform-toast-root" style="z-index: 1080;"></div>

@hasSection('footer')
    @yield('footer')
@else
    @include('partials.footer')
@endif

@include('partials.scripts')
<script>
    (function () {
        const toastRoot = document.getElementById('platform-toast-root');
        if (toastRoot) {
            const successMessage = @json(session('success'));
            const errorMessage = @json(session('error'));

            [
                { type: 'success', message: successMessage },
                { type: 'danger', message: errorMessage },
            ].forEach(function (toastData) {
                if (!toastData.message) {
                    return;
                }

                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-bg-' + toastData.type + ' border-0 mb-2';
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.innerHTML =
                    '<div class="d-flex">' +
                    '<div class="toast-body">' + toastData.message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                    '</div>';

                toastRoot.appendChild(toastEl);
                new bootstrap.Toast(toastEl, { delay: 4000 }).show();
            });
        }

        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const confirmText = form.getAttribute('data-confirm');
                if (confirmText && !window.confirm(confirmText)) {
                    event.preventDefault();
                    return;
                }

                const submitButton = form.querySelector('button[type="submit"], .platform-submit-btn');
                if (!submitButton || submitButton.classList.contains('is-loading')) {
                    return;
                }

                submitButton.classList.add('is-loading');
                submitButton.setAttribute('disabled', 'disabled');
                const loadingText = submitButton.getAttribute('data-loading-text');
                if (loadingText) {
                    const content = submitButton.querySelector('.btn-content');
                    if (content) {
                        content.dataset.originalText = content.textContent;
                        content.textContent = loadingText;
                    }
                }
            });
        });
    })();
</script>
@yield('page_scripts')

</body>
</html>
