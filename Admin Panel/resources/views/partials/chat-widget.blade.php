{{--
    Plantix AI — Floating Chat Widget  (v2 — polished UI)
    Injected globally via layouts/frontend.blade.php
    Routes: chat.widget.message / chat.widget.new / chat.widget.history
--}}
<style>
/* ── Reset inside widget ──────────────────────────────────────────────────── */
#px-chat-widget, #px-chat-widget * { box-sizing: border-box; }

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
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    border: none; cursor: pointer;
    box-shadow: 0 4px 16px rgba(46,125,50,.45);
    display: flex; align-items: center; justify-content: center;
    transition: transform .2s, box-shadow .2s;
    position: relative;
}
#px-chat-toggle:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(46,125,50,.55); }
#px-chat-toggle svg { width: 26px; height: 26px; fill: #fff; transition: opacity .2s; position: absolute; }
#px-chat-toggle .px-icon-chat  { opacity: 1; }
#px-chat-toggle .px-icon-close { opacity: 0; }
#px-chat-widget.open #px-chat-toggle .px-icon-chat  { opacity: 0; }
#px-chat-widget.open #px-chat-toggle .px-icon-close { opacity: 1; }

/* Unread badge */
#px-chat-badge {
    position: absolute; top: -4px; right: -4px;
    background: #e53935; color: #fff;
    font-size: 10px; font-weight: 700;
    min-width: 18px; height: 18px; border-radius: 9px;
    display: none; align-items: center; justify-content: center;
    padding: 0 4px; border: 2px solid #fff;
}

/* ── Chat panel ───────────────────────────────────────────────────────────── */
#px-chat-panel {
    position: absolute; bottom: 68px; right: 0;
    width: 370px; height: 540px;
    background: #fff; border-radius: 18px;
    box-shadow: 0 12px 48px rgba(0,0,0,.2);
    display: flex; flex-direction: column;
    overflow: hidden;
    transform: scale(.92) translateY(14px);
    opacity: 0; pointer-events: none;
    transition: transform .24s cubic-bezier(.34,1.56,.64,1), opacity .18s ease;
}
#px-chat-widget.open #px-chat-panel {
    transform: scale(1) translateY(0);
    opacity: 1; pointer-events: all;
}

/* ── Header ───────────────────────────────────────────────────────────────── */
#px-chat-header {
    background: linear-gradient(135deg, #1b5e20, #2e7d32, #388e3c);
    padding: 13px 16px;
    display: flex; align-items: center; gap: 11px;
    flex-shrink: 0;
}
#px-chat-header .px-avatar {
    width: 38px; height: 38px;
    background: rgba(255,255,255,.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; border: 2px solid rgba(255,255,255,.35);
}
#px-chat-header .px-avatar svg { width: 20px; height: 20px; fill: #fff; }
#px-chat-header .px-title { flex: 1; min-width: 0; }
#px-chat-header .px-title h6 { margin: 0; color: #fff; font-size: 14px; font-weight: 700; line-height: 1.2; }
#px-chat-header .px-title span { color: rgba(255,255,255,.75); font-size: 11px; }
#px-chat-header .px-title span::before {
    content: ''; display: inline-block;
    width: 7px; height: 7px; border-radius: 50%;
    background: #69f0ae; margin-right: 4px; vertical-align: middle;
}
#px-chat-header-actions button {
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 8px; color: #fff;
    font-size: 11px; font-weight: 600;
    padding: 5px 10px; cursor: pointer;
    transition: background .15s; white-space: nowrap;
}
#px-chat-header-actions button:hover { background: rgba(255,255,255,.28); }

/* ── Messages area ────────────────────────────────────────────────────────── */
#px-chat-messages {
    flex: 1; overflow-y: auto;
    padding: 16px 14px 8px;
    display: flex; flex-direction: column; gap: 12px;
    scroll-behavior: smooth; background: #f8fdf8;
}
#px-chat-messages::-webkit-scrollbar { width: 4px; }
#px-chat-messages::-webkit-scrollbar-track { background: transparent; }
#px-chat-messages::-webkit-scrollbar-thumb { background: #c8e6c9; border-radius: 4px; }

/* ── Message rows ─────────────────────────────────────────────────────────── */
.px-msg { display: flex; gap: 8px; animation: px-fade-in .2s ease; max-width: 100%; }
@keyframes px-fade-in { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.px-msg.user { align-self: flex-end;   flex-direction: row-reverse; max-width: 82%; }
.px-msg.bot  { align-self: flex-start; max-width: 90%; }

.px-msg-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 2px;
}
.px-msg.bot  .px-msg-avatar { background: #e8f5e9; }
.px-msg.user .px-msg-avatar { background: #e3f2fd; }
.px-msg-avatar svg { width: 16px; height: 16px; }
.px-msg.bot  .px-msg-avatar svg { fill: #2e7d32; }
.px-msg.user .px-msg-avatar svg { fill: #1565c0; }

.px-msg-bubble {
    padding: 10px 13px; border-radius: 16px;
    font-size: 13px; line-height: 1.6; word-break: break-word;
}
.px-msg.bot .px-msg-bubble {
    background: #fff; color: #1a2e1a;
    border-bottom-left-radius: 4px;
    border: 1px solid #e0f0e0;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.px-msg.user .px-msg-bubble {
    background: linear-gradient(135deg, #2e7d32, #43a047);
    color: #fff; border-bottom-right-radius: 4px;
    box-shadow: 0 2px 8px rgba(46,125,50,.3);
}

/* Markdown inside bot bubbles */
.px-msg.bot .px-msg-bubble strong { font-weight: 700; color: #1b5e20; }
.px-msg.bot .px-msg-bubble em     { font-style: italic; }
.px-msg.bot .px-msg-bubble code {
    background: #e8f5e9; color: #1b5e20;
    padding: 1px 5px; border-radius: 4px;
    font-family: 'Courier New', monospace; font-size: 12px;
}
.px-msg.bot .px-msg-bubble ul,
.px-msg.bot .px-msg-bubble ol { margin: 6px 0 4px 18px; padding: 0; }
.px-msg.bot .px-msg-bubble li { margin-bottom: 3px; }
.px-msg.bot .px-msg-bubble p  { margin: 0 0 6px; }
.px-msg.bot .px-msg-bubble p:last-child { margin-bottom: 0; }
.px-msg.bot .px-msg-bubble h4 {
    font-size: 13px; font-weight: 700; color: #1b5e20;
    margin: 8px 0 4px; border-bottom: 1px solid #c8e6c9; padding-bottom: 2px;
}
.px-msg.bot .px-msg-bubble hr { border: none; border-top: 1px solid #e0f0e0; margin: 8px 0; }

/* Typing indicator */
.px-typing .px-msg-bubble {
    display: flex; gap: 5px; align-items: center;
    padding: 12px 16px; background: #fff; border: 1px solid #e0f0e0;
}
.px-typing .px-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #81c784;
    animation: px-bounce .9s infinite ease-in-out;
}
.px-typing .px-dot:nth-child(2) { animation-delay: .18s; }
.px-typing .px-dot:nth-child(3) { animation-delay: .36s; }
@keyframes px-bounce { 0%,80%,100%{transform:scale(.65);opacity:.4} 40%{transform:scale(1);opacity:1} }

/* Quick suggestions */
#px-chat-suggestions {
    padding: 6px 12px 8px;
    display: flex; flex-wrap: wrap; gap: 6px;
    flex-shrink: 0; background: #f8fdf8;
    border-top: 1px solid #e8f5e9;
}
.px-suggestion {
    background: #fff; border: 1.5px solid #a5d6a7;
    border-radius: 14px; padding: 5px 11px;
    font-size: 11.5px; color: #2e7d32; font-weight: 500;
    cursor: pointer; transition: all .15s; white-space: nowrap;
}
.px-suggestion:hover { background: #e8f5e9; border-color: #66bb6a; }

/* Input area */
#px-chat-input-area {
    padding: 10px 12px 12px;
    border-top: 1px solid #e8f5e9;
    display: flex; gap: 8px; align-items: flex-end;
    flex-shrink: 0; background: #fff;
}
#px-chat-input {
    flex: 1; min-width: 0;
    border: 1.5px solid #c8e6c9; border-radius: 20px;
    padding: 9px 14px; font-size: 13px;
    resize: none; overflow: hidden; outline: none;
    max-height: 88px; line-height: 1.45;
    background: #f8fdf8; color: #1a2e1a;
    transition: border-color .15s, background .15s;
    font-family: inherit;
}
#px-chat-input:focus { border-color: #43a047; background: #fff; }
#px-chat-input::placeholder { color: #9e9e9e; }

/* Send button — fixed 36×36 circle */
#px-chat-send {
    width: 36px; height: 36px; min-width: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2e7d32, #43a047);
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: transform .15s, opacity .15s, box-shadow .15s;
    box-shadow: 0 2px 8px rgba(46,125,50,.35);
}
#px-chat-send:hover:not(:disabled) { transform: scale(1.1); box-shadow: 0 4px 12px rgba(46,125,50,.45); }
#px-chat-send:disabled { opacity: .4; cursor: default; box-shadow: none; }
#px-chat-send svg { width: 15px; height: 15px; fill: #fff; margin-left: 1px; }

@media (max-width: 420px) {
    #px-chat-panel { width: calc(100vw - 32px); height: 480px; right: 0; }
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
                {{-- Leaf / plant icon --}}
                <svg viewBox="0 0 24 24"><path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 0 0 8 20C19 20 22 3 22 3c-1 2-8 2-8 2 1-3 4-3 4-3C13 2 9 5 9 9c0 1.17.27 2.28.72 3.28L12 10l1 2-2.5 1.5C10.18 14.5 10 15.24 10 16c0 2.21 1.79 4 4 4 .5 0 .97-.1 1.41-.26L17 8z"/></svg>
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

    /* SVG icons */
    const BOT_SVG  = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 0 0 8 20C19 20 22 3 22 3c-1 2-8 2-8 2 1-3 4-3 4-3C13 2 9 5 9 9c0 1.17.27 2.28.72 3.28L12 10l1 2-2.5 1.5C10.18 14.5 10 15.24 10 16c0 2.21 1.79 4 4 4 .5 0 .97-.1 1.41-.26L17 8z"/></svg>';
    const USER_SVG = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>';

    /* ── State ───────────────────────────────────────────────────────────── */
    let isOpen        = false;
    let isSending     = false;
    let unread        = 0;
    let historyLoaded = false;

    /* ── DOM refs ────────────────────────────────────────────────────────── */
    const widget      = document.getElementById('px-chat-widget');
    const toggle      = document.getElementById('px-chat-toggle');
    const messages    = document.getElementById('px-chat-messages');
    const input       = document.getElementById('px-chat-input');
    const sendBtn     = document.getElementById('px-chat-send');
    const clearBtn    = document.getElementById('px-chat-clear');
    const badge       = document.getElementById('px-chat-badge');
    const suggestions = document.getElementById('px-chat-suggestions');

    /* ── Lightweight markdown → HTML ─────────────────────────────────────── */
    function renderMarkdown(text) {
        // Escape HTML first to prevent XSS
        let s = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Headings (### ## #)
        s = s.replace(/^### (.+)$/gm, '<h4>$1</h4>');
        s = s.replace(/^## (.+)$/gm,  '<h4>$1</h4>');
        s = s.replace(/^# (.+)$/gm,   '<h4>$1</h4>');

        // Bold **text** or __text__
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        s = s.replace(/__(.+?)__/g,     '<strong>$1</strong>');

        // Italic *text* or _text_
        s = s.replace(/\*([^*\n]+?)\*/g, '<em>$1</em>');
        s = s.replace(/_([^_\n]+?)_/g,   '<em>$1</em>');

        // Inline code `code`
        s = s.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Horizontal rule ---
        s = s.replace(/^---+$/gm, '<hr>');

        // Unordered lists (lines starting with - or *)
        s = s.replace(/^[\*\-] (.+)$/gm, '<li>$1</li>');
        s = s.replace(/(<li>.*<\/li>)/s, function(m) {
            return '<ul>' + m + '</ul>';
        });

        // Ordered lists (lines starting with 1. 2. etc.)
        s = s.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

        // Wrap consecutive <li> not already in <ul> into <ol>
        s = s.replace(/(?<!<\/ul>)(<li>(?:(?!<ul>|<\/ul>)[\s\S])*?<\/li>)+(?!<\/ul>)/g, function(m) {
            if (m.indexOf('<ul>') === -1) return '<ol>' + m + '</ol>';
            return m;
        });

        // Paragraphs — split on double newlines
        const blocks = s.split(/\n{2,}/);
        s = blocks.map(function(block) {
            block = block.trim();
            if (!block) return '';
            // Don't wrap block-level elements in <p>
            if (/^<(h[1-6]|ul|ol|li|hr|blockquote)/.test(block)) return block;
            // Replace single newlines with <br> inside paragraphs
            return '<p>' + block.replace(/\n/g, '<br>') + '</p>';
        }).join('');

        return s;
    }

    /* ── Toggle open/close ───────────────────────────────────────────────── */
    toggle.addEventListener('click', function () {
        isOpen = !isOpen;
        widget.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen);
        if (isOpen) {
            clearBadge();
            if (!historyLoaded) loadHistory();
            setTimeout(function () { input.focus(); }, 220);
        }
    });

    /* ── Input auto-resize & send enable ─────────────────────────────────── */
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 88) + 'px';
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
        const isBot = role === 'bot';

        const wrap = document.createElement('div');
        wrap.className = 'px-msg ' + role;

        const avatar = document.createElement('div');
        avatar.className = 'px-msg-avatar';
        avatar.innerHTML = isBot ? BOT_SVG : USER_SVG;

        const bubble = document.createElement('div');
        bubble.className = 'px-msg-bubble';

        if (isBot) {
            // Render markdown for bot messages
            bubble.innerHTML = renderMarkdown(text);
        } else {
            // Plain text for user messages (already escaped in renderMarkdown if needed)
            bubble.textContent = text;
        }

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
            '<div class="px-msg-avatar">' + BOT_SVG + '</div>' +
            '<div class="px-msg-bubble">' +
            '<span class="px-dot"></span><span class="px-dot"></span><span class="px-dot"></span>' +
            '</div>';
        messages.appendChild(wrap);
        scrollBottom();
        return wrap;
    }

    function appendWelcome() {
        appendBubble('bot', 'Asalaamu Alaikum! I\'m **Plantix AI** — your agriculture assistant.\n\nAsk me about:\n- Crops, fertilizers & soil\n- Plant diseases & pests\n- Irrigation & crop planning\n- Farm economics in Pakistan\n\nHow can I help you today?');
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
