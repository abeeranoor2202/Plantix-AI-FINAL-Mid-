(function () {
    function ensureOverlay() {
        var id = 'platform-api-loading-overlay';
        var existing = document.getElementById(id);
        if (existing) {
            return existing;
        }

        var overlay = document.createElement('div');
        overlay.id = id;
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.background = 'rgba(15, 23, 42, 0.18)';
        overlay.style.backdropFilter = 'blur(1px)';
        overlay.style.display = 'none';
        overlay.style.zIndex = '2000';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.innerHTML = '<div style="background:#fff;padding:10px 14px;border-radius:10px;font-weight:700;box-shadow:0 6px 20px rgba(0,0,0,0.15);">Loading...</div>';
        document.body.appendChild(overlay);
        return overlay;
    }

    function setLoading(isLoading) {
        var overlay = ensureOverlay();
        overlay.style.display = isLoading ? 'flex' : 'none';
    }

    function showMessage(type, message) {
        if (!message) {
            return;
        }

        if (window.bootstrap && bootstrap.Toast) {
            var container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '2100';
                document.body.appendChild(container);
            }

            var toast = document.createElement('div');
            toast.className = 'toast align-items-center border-0 text-bg-' + (type === 'error' ? 'danger' : 'success');
            toast.innerHTML = '<div class="d-flex"><div class="toast-body fw-semibold"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            toast.querySelector('.toast-body').textContent = message;
            container.appendChild(toast);
            var t = new bootstrap.Toast(toast, { delay: 3500 });
            t.show();
            toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
            return;
        }

        window.alert(message);
    }

    async function request(url, options) {
        var opts = options || {};
        var method = (opts.method || 'GET').toUpperCase();
        var headers = Object.assign({
            'Accept': 'application/json'
        }, opts.headers || {});

        if (opts.token) {
            headers.Authorization = 'Bearer ' + opts.token;
        }

        if (opts.body && !(opts.body instanceof FormData) && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(opts.body);
        }

        setLoading(true);

        try {
            var response = await fetch(url, {
                method: method,
                headers: headers,
                body: opts.body,
            });

            var payload = null;
            try {
                payload = await response.json();
            } catch (e) {
                payload = null;
            }

            if (!response.ok || !payload || payload.success !== true) {
                var message = payload && payload.message ? payload.message : 'Request failed.';
                showMessage('error', message);
                return { ok: false, status: response.status, payload: payload };
            }

            if (opts.toastSuccess !== false) {
                showMessage('success', payload.message || 'Success');
            }

            return { ok: true, status: response.status, payload: payload };
        } finally {
            setLoading(false);
        }
    }

    window.PlatformApi = {
        request: request,
        setLoading: setLoading,
        showMessage: showMessage,
    };
})();
