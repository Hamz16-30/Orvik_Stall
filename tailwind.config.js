import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                border: 'hsl(var(--border))',
                ring: 'hsl(var(--ring))',
            },
            borderRadius: {
                DEFAULT: 'var(--radius)',
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            fontFamily: {
                display: ['"Instrument Serif"', 'serif', ...defaultTheme.fontFamily.serif],
                body: ['"Inter"', 'sans-serif', ...defaultTheme.fontFamily.sans],
                sans: ['"Inter"', 'sans-serif', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                dashboard: 'var(--shadow-dashboard)',
            },
            animation: {
                'fade-up-1': 'fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'fade-up-2': 'fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards',
                'fade-up-3': 'fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards',
                'fade-up-4': 'fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards',
                'fade-up-5': 'fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards',
            },
            keyframes: {
                fadeUp: {
                    '0%': { opacity: '0', transform: 'translateY(16px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [forms, typography],
};
