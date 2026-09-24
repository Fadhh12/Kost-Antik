import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Design token Kost Antik. Lihat docs/DESIGN.md untuk alasan tiap nilai.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Enums/**/*.php',
        './app/View/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                // Hijau tegel: warna brand.
                tegel: {
                    50: '#E8F1EF',
                    100: '#CFE3DF',
                    200: '#A2C8C1',
                    300: '#6FA9A0',
                    400: '#3F8D84',
                    500: '#23756D',
                    600: '#17645D',
                    700: '#0F4D48',
                    800: '#0B3B37',
                    900: '#082B28',
                    950: '#051C1A',
                },
                // Abu semen: latar & garis.
                kapur: {
                    50: '#F6F7F5',
                    100: '#EDEFEC',
                    200: '#DDE1DD',
                    300: '#C6CCC7',
                },
                // Teks.
                ink: {
                    300: '#A3ADAB',
                    400: '#7C8885',
                    500: '#56625F',
                    700: '#2E3B3A',
                    900: '#14201F',
                },
                // Aksen tunggal.
                kuningan: {
                    100: '#F6EBCF',
                    300: '#E2C27A',
                    500: '#C39A3F',
                    700: '#7F6120',
                },
                jati: {
                    300: '#C9A27E',
                    600: '#7A4E2D',
                    800: '#4E311C',
                },
                // Status.
                success: { DEFAULT: '#2F7A4B', soft: '#E4F2E8', line: '#B9DCC4' },
                warning: { DEFAULT: '#8A5A00', soft: '#FCF1D8', line: '#EED49A' },
                danger: { DEFAULT: '#B23A2A', soft: '#FBE7E3', line: '#F0BFB6' },
                neutral: { DEFAULT: '#56625F', soft: '#EDEFEC', line: '#D3D8D4' },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans Variable"', ...defaultTheme.fontFamily.sans],
                display: ['"Bricolage Grotesque Variable"', '"Plus Jakarta Sans Variable"', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                lg: '0.5rem',
                xl: '0.75rem',
                '2xl': '1rem',
            },
            boxShadow: {
                tile: '0 1px 2px rgb(8 43 40 / 0.06), 0 1px 1px rgb(8 43 40 / 0.04)',
                lift: '0 12px 32px -12px rgb(8 43 40 / 0.22), 0 2px 6px rgb(8 43 40 / 0.06)',
                focus: '0 0 0 3px rgb(195 154 63 / 0.45)',
            },
            transitionTimingFunction: {
                out: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            maxWidth: {
                page: '80rem',
            },
            zIndex: {
                nav: '30',
                drawer: '40',
                modal: '50',
                toast: '60',
            },
        },
    },

    plugins: [forms],
};
