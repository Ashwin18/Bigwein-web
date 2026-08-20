/**
 * BigWein Owner Portal — JS Helpers
 * v2.0
 */

// ─── CSRF-aware fetch helper ───
async function owFetch(url, opts = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = Object.assign({ 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, opts.headers || {});
    if (!(opts.body instanceof FormData) && opts.method !== 'GET') {
        headers['Content-Type'] = 'application/json';
    }
    try {
        const res = await fetch(url, { ...opts, headers });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    } catch (e) {
        console.error('owFetch error:', e);
        return { success: false, message: e.message };
    }
}

// ─── Toast notification ───
let _toastTimer = null;
function showToast(msg, type = 'success') {
    const el = document.getElementById('toast');
    if (!el) return;
    el.textContent = '';
    const icon = document.createElement('i');
    icon.className = type === 'success'
        ? 'fa-solid fa-circle-check'
        : type === 'error'
        ? 'fa-solid fa-circle-xmark'
        : 'fa-solid fa-circle-info';
    const span = document.createElement('span');
    span.textContent = msg;
    el.appendChild(icon);
    el.appendChild(span);
    el.className = 'toast show ' + type;
    if (_toastTimer) clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => { el.className = 'toast'; }, 3500);
}

// ─── Sidebar toggle ───
document.addEventListener('DOMContentLoaded', function () {
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    toggle?.addEventListener('click', function () {
        sidebar?.classList.toggle('open');
        overlay?.classList.toggle('show');
    });
    overlay?.addEventListener('click', function () {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    });

    // Auto-dismiss flash alerts
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

    // Drag & drop for gallery areas
    document.querySelectorAll('.gallery-area').forEach(area => {
        area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
        area.addEventListener('dragleave', () => area.classList.remove('drag-over'));
        area.addEventListener('drop', e => { e.preventDefault(); area.classList.remove('drag-over'); });
    });

    // Radio chip sync
    document.querySelectorAll('.radio-group input[type=radio]').forEach(radio => {
        radio.addEventListener('change', function () {
            const group = this.closest('.radio-group');
            group?.querySelectorAll('.radio-chip').forEach(c => c.classList.remove('checked'));
            this.closest('.radio-chip')?.classList.add('checked');
        });
    });

    // Amenity chips
    document.querySelectorAll('.amenity-chip input[type=checkbox]').forEach(chk => {
        chk.addEventListener('change', function () {
            this.closest('.amenity-chip')?.classList.toggle('checked', this.checked);
        });
    });
});
