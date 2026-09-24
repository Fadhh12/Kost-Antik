import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/*
 * Cegah double submit (SRS 2.4): saat form dikirim, tombol submit
 * dinonaktifkan dan menampilkan spinner. Tambahkan data-no-lock pada form
 * untuk pengecualian (mis. form filter GET).
 */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-lock')) {
        return;
    }

    // Beri kesempatan validasi HTML5 & handler lain berjalan dulu.
    setTimeout(() => {
        if (event.defaultPrevented) {
            return;
        }
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = true;
            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');
        });
    }, 0);
});

// Tombol kembali dari bfcache: aktifkan lagi tombol yang terkunci.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }
    document.querySelectorAll('button.is-loading').forEach((button) => {
        button.disabled = false;
        button.classList.remove('is-loading');
        button.removeAttribute('aria-busy');
    });
});

Alpine.start();
