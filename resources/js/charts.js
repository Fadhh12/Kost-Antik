/*
 * Grafik pendapatan 6 bulan (Chart.js). Hanya dimuat di dashboard admin.
 * <canvas data-revenue-chart data-url="..."></canvas>
 */
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const rupiah = (n) => 'Rp' + Number(n).toLocaleString('id-ID');
const compact = (n) => {
    if (n >= 1_000_000) return (n / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
    if (n >= 1_000) return (n / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
    return String(n);
};

// Warna mengikuti token Tailwind (tegel-800 bulan berjalan, tegel-400 bulan lalu, kapur-200, ink-500).
const COLORS = {
    current: '#0B3B37',
    past: '#3F8D84',
    grid: '#DDE1DD',
    text: '#56625F',
};

async function renderRevenue(canvas) {
    const status = canvas.parentElement.querySelector('[data-chart-status]');
    try {
        const response = await fetch(canvas.dataset.url, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(response.statusText);
        const { labels, values } = await response.json();

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: values.map((_, i) => (i === values.length - 1 ? COLORS.current : COLORS.past)),
                    hoverBackgroundColor: COLORS.current,
                    // Ujung data membulat 4px, menempel di baseline.
                    borderRadius: { topLeft: 4, topRight: 4 },
                    borderSkipped: 'start',
                    maxBarThickness: 44,
                }],
            },
            options: {
                maintainAspectRatio: false,
                animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 500 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#082B28',
                        padding: 10,
                        displayColors: false,
                        callbacks: { label: (ctx) => rupiah(ctx.parsed.y) },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: COLORS.text, font: { family: 'Plus Jakarta Sans Variable' } } },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: COLORS.grid },
                        ticks: { color: COLORS.text, callback: (v) => compact(v), maxTicksLimit: 5 },
                    },
                },
            },
        });

        // Ringkasan teks untuk pembaca layar.
        canvas.setAttribute('aria-label', 'Pendapatan per bulan: ' + labels.map((l, i) => `${l} ${rupiah(values[i])}`).join(', '));
        status?.remove();
    } catch (error) {
        if (status) status.textContent = 'Grafik gagal dimuat. Muat ulang halaman untuk mencoba lagi.';
    }
}

document.querySelectorAll('[data-revenue-chart]').forEach(renderRevenue);
