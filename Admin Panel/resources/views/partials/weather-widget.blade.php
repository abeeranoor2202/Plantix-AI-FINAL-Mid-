{{--
    Plantix AI — Floating Weather Widget
    Sits above the chat toggle button (bottom-right corner).
    Routes: weather.widget / weather.widget.cities
--}}

<style>
/* ── Weather widget container ─────────────────────────────────────────────── */
#px-weather-widget {
    position: fixed;
    bottom: 92px;   /* sits above the 56px chat button + 24px gap */
    right: 24px;
    z-index: 9998;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ── Collapsed pill (always visible) ─────────────────────────────────────── */
#px-weather-pill {
    display: flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,.96);
    border: 1.5px solid #c8e6c9;
    border-radius: 24px;
    padding: 6px 14px 6px 10px;
    cursor: pointer;
    box-shadow: 0 2px 12px rgba(0,0,0,.12);
    transition: box-shadow .2s, transform .2s;
    user-select: none;
    white-space: nowrap;
}
#px-weather-pill:hover { box-shadow: 0 4px 18px rgba(0,0,0,.18); transform: translateY(-1px); }
#px-weather-pill img.px-w-icon { width: 28px; height: 28px; }
#px-weather-pill .px-w-temp  { font-size: 15px; font-weight: 700; color: #1b5e20; }
#px-weather-pill .px-w-city  { font-size: 11px; color: #555; max-width: 90px; overflow: hidden; text-overflow: ellipsis; }
#px-weather-pill .px-w-caret {
    font-size: 10px; color: #888; margin-left: 2px;
    transition: transform .2s;
}
#px-weather-widget.open #px-weather-pill .px-w-caret { transform: rotate(180deg); }

/* Loading skeleton in pill */
.px-w-skeleton {
    width: 60px; height: 14px;
    background: linear-gradient(90deg, #e8f5e9 25%, #c8e6c9 50%, #e8f5e9 75%);
    background-size: 200% 100%;
    animation: px-shimmer 1.2s infinite;
    border-radius: 6px;
}
@keyframes px-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Expanded panel ───────────────────────────────────────────────────────── */
#px-weather-panel {
    position: absolute;
    bottom: calc(100% + 10px);
    right: 0;
    width: 320px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,.16);
    overflow: hidden;
    transform: scale(.94) translateY(10px);
    opacity: 0;
    pointer-events: none;
    transition: transform .22s cubic-bezier(.34,1.56,.64,1), opacity .18s ease;
}
#px-weather-widget.open #px-weather-panel {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* Panel header */
#px-weather-panel-header {
    background: linear-gradient(135deg, #1565c0, #1976d2);
    padding: 16px;
    color: #fff;
}
#px-weather-panel-header .px-w-search {
    display: flex;
    gap: 6px;
    margin-bottom: 14px;
}
#px-weather-panel-header .px-w-search input {
    flex: 1;
    border: none;
    border-radius: 20px;
    padding: 7px 14px;
    font-size: 13px;
    outline: none;
    background: rgba(255,255,255,.2);
    color: #fff;
}
#px-weather-panel-header .px-w-search input::placeholder { color: rgba(255,255,255,.7); }
#px-weather-panel-header .px-w-search button {
    background: rgba(255,255,255,.25);
    border: none;
    border-radius: 20px;
    padding: 7px 14px;
    color: #fff;
    font-size: 12px;
    cursor: pointer;
    transition: background .15s;
}
#px-weather-panel-header .px-w-search button:hover { background: rgba(255,255,255,.4); }

/* Current weather block */
.px-w-current {
    display: flex;
    align-items: center;
    gap: 12px;
}
.px-w-current img { width: 56px; height: 56px; }
.px-w-current .px-w-main-temp {
    font-size: 36px;
    font-weight: 800;
    line-height: 1;
}
.px-w-current .px-w-desc { font-size: 13px; opacity: .85; text-transform: capitalize; margin-top: 2px; }
.px-w-current .px-w-loc  { font-size: 12px; opacity: .7; margin-top: 2px; }

/* Stats row */
.px-w-stats {
    display: flex;
    gap: 0;
    background: rgba(255,255,255,.12);
    border-radius: 10px;
    margin-top: 12px;
    overflow: hidden;
}
.px-w-stat {
    flex: 1;
    text-align: center;
    padding: 8px 4px;
    font-size: 11px;
    border-right: 1px solid rgba(255,255,255,.15);
}
.px-w-stat:last-child { border-right: none; }
.px-w-stat strong { display: block; font-size: 13px; font-weight: 700; }

/* Agriculture alert */
.px-w-alert {
    margin: 10px 14px 0;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    line-height: 1.4;
    display: none;
}
.px-w-alert.show { display: block; }
.px-w-alert.high, .px-w-alert.extreme { background: #ffebee; color: #b71c1c; border-left: 3px solid #e53935; }
.px-w-alert.moderate { background: #fff8e1; color: #e65100; border-left: 3px solid #ffa000; }

/* 5-day forecast */
.px-w-forecast {
    padding: 12px 14px 14px;
}
.px-w-forecast h6 {
    font-size: 11px;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0 0 8px;
}
.px-w-days {
    display: flex;
    gap: 6px;
}
.px-w-day {
    flex: 1;
    text-align: center;
    background: #f1f8e9;
    border-radius: 10px;
    padding: 8px 4px;
    font-size: 11px;
    color: #2e7d32;
}
.px-w-day img { width: 28px; height: 28px; }
.px-w-day .px-w-day-name { font-weight: 700; margin-bottom: 2px; }
.px-w-day .px-w-day-max  { font-size: 12px; font-weight: 700; }
.px-w-day .px-w-day-min  { font-size: 10px; color: #888; }

/* City suggestions dropdown */
#px-w-city-suggestions {
    position: absolute;
    top: 100%;
    left: 0; right: 0;
    background: #fff;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
    z-index: 10;
    max-height: 180px;
    overflow-y: auto;
    display: none;
}
#px-w-city-suggestions.show { display: block; }
.px-w-city-opt {
    padding: 9px 14px;
    font-size: 13px;
    cursor: pointer;
    color: #333;
    border-bottom: 1px solid #f5f5f5;
    transition: background .12s;
}
.px-w-city-opt:hover { background: #e8f5e9; }

/* Geolocation button */
#px-w-geo-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 7px 10px;
    border-radius: 20px;
    background: rgba(255,255,255,.2);
    color: #fff;
    font-size: 14px;
    transition: background .15s;
    flex-shrink: 0;
}
#px-w-geo-btn:hover { background: rgba(255,255,255,.35); }

/* Error state */
.px-w-error {
    padding: 20px;
    text-align: center;
    color: #888;
    font-size: 13px;
}

@media (max-width: 420px) {
    #px-weather-panel { width: calc(100vw - 32px); right: 0; }
    #px-weather-widget { right: 16px; bottom: 88px; }
}
</style>

<div id="px-weather-widget" role="complementary" aria-label="Weather Widget">

    {{-- Collapsed pill --}}
    <div id="px-weather-pill" role="button" tabindex="0" aria-expanded="false" aria-controls="px-weather-panel" aria-label="Toggle weather panel">
        <img class="px-w-icon" src="https://openweathermap.org/img/wn/01d.png" alt="Weather icon" id="px-pill-icon">
        <div>
            <div class="px-w-temp" id="px-pill-temp"><span class="px-w-skeleton"></span></div>
            <div class="px-w-city" id="px-pill-city">Loading…</div>
        </div>
        <span class="px-w-caret" aria-hidden="true">▼</span>
    </div>

    {{-- Expanded panel --}}
    <div id="px-weather-panel" role="dialog" aria-modal="false" aria-label="Weather details">

        {{-- Header with search --}}
        <div id="px-weather-panel-header">
            <div class="px-w-search" style="position:relative;">
                <input type="text" id="px-w-city-input" placeholder="Search city…" autocomplete="off" aria-label="Search city" aria-autocomplete="list" aria-controls="px-w-city-suggestions">
                <button id="px-w-geo-btn" title="Use my location" aria-label="Use my location">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                </button>
                <div id="px-w-city-suggestions" role="listbox" aria-label="City suggestions"></div>
            </div>

            {{-- Current weather --}}
            <div class="px-w-current" id="px-w-current-block">
                <img id="px-w-main-icon" src="https://openweathermap.org/img/wn/01d@2x.png" alt="Weather condition icon">
                <div>
                    <div class="px-w-main-temp" id="px-w-main-temp">--°</div>
                    <div class="px-w-desc" id="px-w-desc">--</div>
                    <div class="px-w-loc"  id="px-w-loc">--</div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="px-w-stats">
                <div class="px-w-stat">
                    <strong id="px-w-feels">--</strong>
                    Feels like
                </div>
                <div class="px-w-stat">
                    <strong id="px-w-humidity">--</strong>
                    Humidity
                </div>
                <div class="px-w-stat">
                    <strong id="px-w-wind">--</strong>
                    Wind
                </div>
                <div class="px-w-stat">
                    <strong id="px-w-rain">--</strong>
                    Rain
                </div>
            </div>
        </div>

        {{-- Agriculture alert --}}
        <div class="px-w-alert" id="px-w-alert" role="alert" aria-live="polite"></div>

        {{-- 5-day forecast --}}
        <div class="px-w-forecast">
            <h6>5-Day Forecast</h6>
            <div class="px-w-days" id="px-w-days" aria-label="5-day forecast"></div>
        </div>

    </div>
</div>

<script>
(function () {
    'use strict';

    const WEATHER_URL = '{{ route("weather.widget") }}';
    const CITIES_URL  = '{{ route("weather.widget.cities") }}';
    const OWM_ICON    = 'https://openweathermap.org/img/wn/';

    const widget    = document.getElementById('px-weather-widget');
    const pill      = document.getElementById('px-weather-pill');
    const panel     = document.getElementById('px-weather-panel');
    const cityInput = document.getElementById('px-w-city-input');
    const geoBtn    = document.getElementById('px-w-geo-btn');
    const citySugg  = document.getElementById('px-w-city-suggestions');

    let allCities = [];
    let isOpen    = false;

    /* ── Toggle ──────────────────────────────────────────────────────────── */
    function togglePanel() {
        isOpen = !isOpen;
        widget.classList.toggle('open', isOpen);
        pill.setAttribute('aria-expanded', isOpen);
    }

    pill.addEventListener('click', togglePanel);
    pill.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePanel(); }
    });

    /* Close when clicking outside */
    document.addEventListener('click', function (e) {
        if (isOpen && !widget.contains(e.target)) {
            isOpen = false;
            widget.classList.remove('open');
            pill.setAttribute('aria-expanded', false);
        }
    });

    /* ── Load city list ──────────────────────────────────────────────────── */
    fetch(CITIES_URL)
        .then(function (r) { return r.json(); })
        .then(function (d) { if (d.success) allCities = d.data; })
        .catch(function () {});

    /* ── City search autocomplete ────────────────────────────────────────── */
    cityInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        citySugg.innerHTML = '';
        if (q.length < 1) { citySugg.classList.remove('show'); return; }

        const matches = allCities.filter(function (c) { return c.toLowerCase().includes(q); }).slice(0, 8);
        if (!matches.length) { citySugg.classList.remove('show'); return; }

        matches.forEach(function (city) {
            const opt = document.createElement('div');
            opt.className = 'px-w-city-opt';
            opt.textContent = city;
            opt.setAttribute('role', 'option');
            opt.addEventListener('click', function () {
                cityInput.value = '';
                citySugg.classList.remove('show');
                loadWeather({ city: city });
            });
            citySugg.appendChild(opt);
        });
        citySugg.classList.add('show');
    });

    cityInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && this.value.trim()) {
            citySugg.classList.remove('show');
            loadWeather({ city: this.value.trim() });
            this.value = '';
        }
        if (e.key === 'Escape') { citySugg.classList.remove('show'); }
    });

    /* ── Geolocation ─────────────────────────────────────────────────────── */
    geoBtn.addEventListener('click', function () {
        if (!navigator.geolocation) { return; }
        geoBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                geoBtn.disabled = false;
                loadWeather({ lat: pos.coords.latitude, lon: pos.coords.longitude });
            },
            function () { geoBtn.disabled = false; }
        );
    });

    /* ── Load weather ────────────────────────────────────────────────────── */
    function loadWeather(params) {
        // Show loading state in pill
        document.getElementById('px-pill-temp').innerHTML = '<span class="px-w-skeleton"></span>';
        document.getElementById('px-pill-city').textContent = 'Loading…';

        const url = new URL(WEATHER_URL, window.location.origin);
        if (params.city) url.searchParams.set('city', params.city);
        if (params.lat)  { url.searchParams.set('lat', params.lat); url.searchParams.set('lon', params.lon); }

        fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    renderWeather(d.data);
                    renderAlerts(d.alerts);
                } else {
                    document.getElementById('px-pill-temp').textContent = '--';
                    document.getElementById('px-pill-city').textContent = params.city || 'Error';
                }
            })
            .catch(function () {
                document.getElementById('px-pill-temp').textContent = '--';
                document.getElementById('px-pill-city').textContent = 'Unavailable';
                document.getElementById('px-w-current-block').innerHTML =
                    '<div class="px-w-error">Could not load weather data. Check your connection.</div>';
            });
    }

    /* ── Render ──────────────────────────────────────────────────────────── */
    function renderWeather(w) {
        const icon = w.icon_code ? OWM_ICON + w.icon_code + '.png' : OWM_ICON + '01d.png';
        const icon2x = w.icon_code ? OWM_ICON + w.icon_code + '@2x.png' : OWM_ICON + '01d@2x.png';
        const temp = w.temperature_c !== null ? Math.round(w.temperature_c) + '°C' : '--';

        /* Update pill */
        document.getElementById('px-pill-icon').src = icon;
        document.getElementById('px-pill-icon').alt = w.condition || 'Weather';
        document.getElementById('px-pill-temp').textContent = temp;
        document.getElementById('px-pill-city').textContent = w.city || 'Weather';

        /* Update panel header */
        document.getElementById('px-w-main-icon').src = icon2x;
        document.getElementById('px-w-main-icon').alt = w.condition || '';
        document.getElementById('px-w-main-temp').textContent = temp;
        document.getElementById('px-w-desc').textContent = w.condition || '--';
        document.getElementById('px-w-loc').textContent = w.city ? '📍 ' + w.city + ', Pakistan' : '';

        /* Stats */
        document.getElementById('px-w-feels').textContent    = w.feels_like_c !== null ? Math.round(w.feels_like_c) + '°C' : '--';
        document.getElementById('px-w-humidity').textContent = w.humidity !== null ? w.humidity + '%' : '--';
        document.getElementById('px-w-wind').textContent     = w.wind_speed_kmh !== null ? w.wind_speed_kmh + ' km/h' : '--';
        document.getElementById('px-w-rain').textContent     = w.rainfall_mm !== null ? w.rainfall_mm + ' mm' : '0 mm';

        /* 5-day forecast */
        const daysEl = document.getElementById('px-w-days');
        daysEl.innerHTML = '';
        const days = (w.daily_forecast || []).slice(0, 5);
        if (days.length) {
            days.forEach(function (d) {
                const dayName = new Date(d.date).toLocaleDateString('en-PK', { weekday: 'short' });
                const dIcon   = d.icon ? OWM_ICON + d.icon + '.png' : OWM_ICON + '01d.png';
                const el = document.createElement('div');
                el.className = 'px-w-day';
                el.innerHTML =
                    '<div class="px-w-day-name">' + dayName + '</div>' +
                    '<img src="' + dIcon + '" alt="' + (d.condition || '') + '">' +
                    '<div class="px-w-day-max">' + (d.max_c !== null ? Math.round(d.max_c) + '°' : '--') + '</div>' +
                    '<div class="px-w-day-min">' + (d.min_c !== null ? Math.round(d.min_c) + '°' : '--') + '</div>';
                daysEl.appendChild(el);
            });
        } else {
            daysEl.innerHTML = '<div style="font-size:12px;color:#aaa;padding:8px 0;">No forecast data</div>';
        }
    }

    function renderAlerts(alerts) {
        const alertEl = document.getElementById('px-w-alert');
        if (!alerts || !alerts.length) {
            alertEl.className = 'px-w-alert';
            alertEl.textContent = '';
            return;
        }
        const top = alerts[0];
        alertEl.className = 'px-w-alert show ' + (top.severity || 'moderate');
        alertEl.innerHTML = '⚠️ <strong>Farm Alert:</strong> ' + top.message;
    }

    /* ── Initial load ────────────────────────────────────────────────────── */
    loadWeather({});   // server picks city from user profile or defaults to Lahore

})();
</script>
