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

/*
 * Reveal saat scroll untuk halaman publik: elemen [data-reveal] muncul
 * perlahan ketika masuk viewport. Tanpa JS / reduced motion: langsung tampil.
 */
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const revealTargets = document.querySelectorAll('[data-reveal]');
if (!reduceMotion && 'IntersectionObserver' in window && revealTargets.length) {
    document.documentElement.classList.add('js-reveal');
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.1 },
    );
    revealTargets.forEach((el) => observer.observe(el));
}

Alpine.start();
