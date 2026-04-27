{{--
    Plantix AI — Floating Chat Widget
    Injected globally via layouts/frontend.blade.php
    Routes: chat.widget.message / chat.widget.new / chat.widget.history
--}}

{{-- ── Styles ──────────────────────────────────────────────────────────────── --}}
<style>
/* ── Widget container ─────────────────────────────────────────────────────── */
#px-chat-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ── Toggle button ────────────────────────────────────────────────────────── */
#px-chat-toggle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(46,125,50,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .2s, box-shadow .2s;
    position: relative;
}
#px-chat-toggle:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(46,125,50,.55); }
#px-chat-toggle svg   { width: 26px; height: 26px; fill: #fff; transition: opacity .2s; }
#px-chat-toggle .px-icon-chat  { opacity: 1; position: absolute; }
#px-chat-toggle .px-icon-close { opacity: 0; position: absolute; }
#px-chat-widget.open #px-chat-toggle .px-icon-chat  { opacity: 0; }
#px-chat-widget.open #px-chat-toggle .px-icon-close { opacity: 1; }

/* Unread badge */
#px-chat-badge {
    position: absolute;
    top: -4px; right: -4px;
    background: #e53935;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    border: 2px solid #fff;
}

/* ── Chat panel ───────────────────────────────────────────────────────────── */
#px-chat-panel {
    position: absolute;
    bottom: 68px;
    right: 0;
    width: 360px;
    max-height: 520px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(.92) translateY(12px);
    opacity: 0;
    pointer-events: none;
    transition: transform .22s cubic-bezier(.34,1.56,.64,1), opacity .18s ease;
}
#px-chat-widget.open #px-chat-panel {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* Header */
#px-chat-header {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
#px-chat-header .px-avatar {
    width: 36px; height: 36px;
    background: rgba(255,255,255,.25);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
#px-chat-header .px-avatar svg { width: 20px; height: 20px; fill: #fff; }
#px-chat-header .px-title { flex: 1; }
#px-chat-header .px-title h6 { margin: 0; color: #fff; font-size: 14px; font-weight: 700; }
#px-chat-header .px-title span { color: rgba(255,255,255,.8); font-size: 11px; }
#px-chat-header-actions { display: flex; gap: 6px; }
#px-chat-header-actions button {
    background: rgba(255,255,255,.18);
    border: none; border-radius: 6px;
    color: #fff; font-size: 11px; padding: 4px 8px;
    cursor: pointer; transition: background .15s;
}
#px-chat-header-actions button:hover { background: rgba(255,255,255,.32); }

/* Messages area */
#px-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 14px 14px 6px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}
#px-chat-messages::-webkit-scrollbar { width: 4px; }
#px-chat-messages::-webkit-scrollbar-thumb { background: #c8e6c9; border-radius: 4px; }

/* Bubbles */
.px-msg {
    display: flex;
    gap: 8px;
    max-width: 88%;
    animation: px-fade-in .18s ease;
}
@keyframes px-fade-in { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
.px-msg.user  { align-self: flex-end; flex-direction: row-reverse; }
.px-msg.bot   { align-self: flex-start; }
.px-msg-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: #e8f5e9; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 2px;
}
.px-msg-avatar svg { width: 15px; height: 15px; fill: #2e7d32; }
.px-msg.user .px-msg-avatar { background: #e3f2fd; }
.px-msg.user .px-msg-avatar svg { fill: #1565c0; }
.px-msg-bubble {
    padding: 9px 12px;
    border-radius: 14px;
    font-size: 13px;
    line-height: 1.5;
    word-break: break-word;
}
.px-msg.bot  .px-msg-bubble { background: #f1f8e9; color: #1b5e20; border-bottom-left-radius: 4px; }
.px-msg.user .px-msg-bubble { background: #1565c0; color: #fff; border-bottom-right-radius: 4px; }

/* Typing indicator */
.px-typing .px-msg-bubble {
    display: flex; gap: 4px; align-items: center; padding: 10px 14px;
}
.px-typing .px-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #66bb6a;
    animation: px-bounce .9s infinite ease-in-out;
}
.px-typing .px-dot:nth-child(2) { animation-delay: .15s; }
.px-typing .px-dot:nth-child(3) { animation-delay: .30s; }
@keyframes px-bounce { 0%,80%,100% { transform:scale(.7); opacity:.5; } 40% { transform:scale(1); opacity:1; } }

/* Input area */
#px-chat-input-area {
    padding: 10px 12px 12px;
    border-top: 1px solid #e8f5e9;
    display: flex;
    gap: 8px;
    align-items: flex-end;
    flex-shrink: 0;
    background: #fafafa;
}
#px-chat-input {
    flex: 1;
    border: 1.5px solid #c8e6c9;
    border-radius: 20px;
    padding: 8px 14px;
    font-size: 13px;
    resize: none;
    outline: none;
    max-height: 90px;
    overflow-y: auto;
    line-height: 1.4;
    background: #fff;
    transition: border-color .15s;
}
#px-chat-input:focus { border-color: #43a047; }
#px-chat-send {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: transform .15s, opacity .15s;
}
#px-chat-send:hover { transform: scale(1.08); }
#px-chat-send:disabled { opacity: .5; cursor: default; transform: none; }
#px-chat-send svg { width: 16px; height: 16px; fill: #fff; }

/* Quick suggestions */
#px-chat-suggestions {
    padding: 0 12px 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
}
.px-suggestion {
    background: #e8f5e9;
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 4px 10px;
    font-size: 11px;
    color: #2e7d32;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
}
.px-suggestion:hover { background: #c8e6c9; }

/* Responsive */
@media (max-width: 420px) {
    #px-chat-panel { width: calc(100vw - 32px); right: 0; }
    #px-chat-widget { bottom: 16px; right: 16px; }
}
</style>

{{-- ── Markup ──────────────────────────────────────────────────────────────── --}}
<div id="px-chat-widget" role="complementary" aria-label="Plantix AI Chat">

    {{-- Toggle button --}}
    <button id="px-chat-toggle" aria-label="Open Plantix AI chat" aria-expanded="false" aria-controls="px-chat-panel">
        {{-- Chat icon --}}
        <svg class="px-icon-chat" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
        </svg>
        {{-- Close icon --}}
        <svg class="px-icon-close" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
        <span id="px-chat-badge" aria-live="polite"></span>
    </button>

    {{-- Chat panel --}}
    <div id="px-chat-panel" role="dialog" aria-modal="false" aria-label="Plantix AI Assistant">

        {{-- Header --}}
        <div id="px-chat-header">
            <div class="px-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
            </div>
            <div class="px-title">
                <h6>Plantix AI</h6>
                <span>Agriculture Assistant</span>
            </div>
            <div id="px-chat-header-actions">
                <button id="px-chat-clear" title="New conversation" aria-label="Start new conversation">New chat</button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="px-chat-messages" role="log" aria-live="polite" aria-label="Chat messages"></div>

        {{-- Quick suggestions (shown only when empty) --}}
        <div id="px-chat-suggestions" aria-label="Quick questions">
            <button class="px-suggestion" type="button">Best crop for loamy soil?</button>
            <button class="px-suggestion" type="button">Wheat fertilizer schedule</button>
            <button class="px-suggestion" type="button">Cotton pest control</button>
            <button class="px-suggestion" type="button">Rabi season crops</button>
        </div>

        {{-- Input --}}
        <div id="px-chat-input-area">
            <textarea
                id="px-chat-input"
                rows="1"
                placeholder="Ask about crops, diseases, fertilizers…"
                aria-label="Type your message"
                maxlength="1000"
            ></textarea>
            <button id="px-chat-send" aria-label="Send message" disabled>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>

    </div>
</div>

{{-- ── Script ──────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    /* ── Config ──────────────────────────────────────────────────────────── */
    const ROUTES = {
        message : '{{ route("chat.widget.message") }}',
        newChat : '{{ route("chat.widget.new") }}',
        history : '{{ route("chat.widget.history") }}',
    };
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── State ───────────────────────────────────────────────────────────── */
    let isOpen      = false;
    let isSending   = false;
    let unread      = 0;
    let historyLoaded = false;

    /* ── DOM refs ────────────────────────────────────────────────────────── */
    const widget      = document.getElementById('px-chat-widget');
    const toggle      = document.getElementById('px-chat-toggle');
    const panel       = document.getElementById('px-chat-panel');
    const messages    = document.getElementById('px-chat-messages');
    const input       = document.getElementById('px-chat-input');
    const sendBtn     = document.getElementById('px-chat-send');
    const clearBtn    = document.getElementById('px-chat-clear');
    const badge       = document.getElementById('px-chat-badge');
    const suggestions = document.getElementById('px-chat-suggestions');

    /* ── Toggle open/close ───────────────────────────────────────────────── */
    toggle.addEventListener('click', function () {
        isOpen = !isOpen;
        widget.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen);

        if (isOpen) {
            clearBadge();
            if (!historyLoaded) loadHistory();
            setTimeout(() => input.focus(), 220);
        }
    });

    /* ── Input auto-resize & send enable ─────────────────────────────────── */
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 90) + 'px';
        sendBtn.disabled = !this.value.trim() || isSending;
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendBtn.disabled) sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    /* ── Quick suggestions ───────────────────────────────────────────────── */
    suggestions.querySelectorAll('.px-suggestion').forEach(function (btn) {
        btn.addEventListener('click', function () {
            input.value = this.textContent.trim();
            input.dispatchEvent(new Event('input'));
            sendMessage();
        });
    });

    /* ── New chat ────────────────────────────────────────────────────────── */
    clearBtn.addEventListener('click', function () {
        fetch(ROUTES.newChat, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
        .then(function (r) { return r.json(); })
        .then(function () {
            messages.innerHTML = '';
            historyLoaded = false;
            showSuggestions(true);
            appendWelcome();
        })
        .catch(function () { appendWelcome(); });
    });

    /* ── Load history ────────────────────────────────────────────────────── */
    function loadHistory() {
        historyLoaded = true;
        fetch(ROUTES.history, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.data && data.data.length > 0) {
                showSuggestions(false);
                data.data.forEach(function (msg) {
                    appendBubble(msg.role === 'user' ? 'user' : 'bot', msg.content, false);
                });
                scrollBottom();
            } else {
                appendWelcome();
            }
        })
        .catch(function () { appendWelcome(); });
    }

    /* ── Send message ────────────────────────────────────────────────────── */
    function sendMessage() {
        const text = input.value.trim();
        if (!text || isSending) return;

        isSending = true;
        sendBtn.disabled = true;
        showSuggestions(false);

        appendBubble('user', text);
        input.value = '';
        input.style.height = 'auto';

        const typing = appendTyping();

        fetch(ROUTES.message, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text, context_type: 'general' }),
        })
        .then(function (r) {
            if (!r.ok) return r.json().then(function (d) { throw new Error(d.message || 'Server error'); });
            return r.json();
        })
        .then(function (data) {
            typing.remove();
            if (data.success) {
                appendBubble('bot', data.response);
                if (!isOpen) showBadge();
            } else {
                appendBubble('bot', 'Sorry, something went wrong. Please try again.');
            }
        })
        .catch(function (err) {
            typing.remove();
            appendBubble('bot', 'Connection error: ' + (err.message || 'Please check your internet and try again.'));
        })
        .finally(function () {
            isSending = false;
            sendBtn.disabled = !input.value.trim();
        });
    }

    /* ── DOM helpers ─────────────────────────────────────────────────────── */
    function appendBubble(role, text, scroll) {
        const wrap = document.createElement('div');
        wrap.className = 'px-msg ' + role;

        const avatar = document.createElement('div');
        avatar.className = 'px-msg-avatar';
        avatar.setAttribute('aria-hidden', 'true');
        avatar.innerHTML = role === 'user'
            ? '<svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>'
            : '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>';

        const bubble = document.createElement('div');
        bubble.className = 'px-msg-bubble';
        bubble.textContent = text;

        wrap.appendChild(avatar);
        wrap.appendChild(bubble);
        messages.appendChild(wrap);

        if (scroll !== false) scrollBottom();
        return wrap;
    }

    function appendTyping() {
        const wrap = document.createElement('div');
        wrap.className = 'px-msg bot px-typing';
        wrap.innerHTML =
            '<div class="px-msg-avatar" aria-hidden="true">' +
            '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>' +
            '</div>' +
            '<div class="px-msg-bubble"><span class="px-dot"></span><span class="px-dot"></span><span class="px-dot"></span></div>';
        messages.appendChild(wrap);
        scrollBottom();
        return wrap;
    }

    function appendWelcome() {
        appendBubble('bot', 'Asalaamu Alaikum! I\'m Plantix AI — your agriculture assistant. Ask me about crops, diseases, fertilizers, irrigation, or anything farming-related in Pakistan. How can I help you today?');
    }

    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function showSuggestions(show) {
        suggestions.style.display = show ? 'flex' : 'none';
    }

    function showBadge() {
        unread++;
        badge.textContent = unread > 9 ? '9+' : unread;
        badge.style.display = 'flex';
    }

    function clearBadge() {
        unread = 0;
        badge.style.display = 'none';
    }

    /* ── Init ────────────────────────────────────────────────────────────── */
    showSuggestions(true);

})();
</script>
