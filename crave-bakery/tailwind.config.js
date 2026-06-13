import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                // Crave Bakery semantic aliases (used in components & brief)
                primary: '#3D1A0E',
                accent: '#E8572A',
                surface: '#FDF6EE',
                card: '#FFFFFF',
                'text-primary': '#1A1A1A',
                'text-muted': '#6B6B6B',
                'border-base': '#E5DDD4',
                success: '#2E7D32',
                error: '#C62828',
                warning: '#EF9F27',
                info: '#185FA5',

                // Artisanal Warmth — Material-style tokens
                'surface-dim': '#dcd9d9',
                'surface-bright': '#fcf9f8',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f6f3f2',
                'surface-container': '#f0eded',
                'surface-container-high': '#eae7e7',
                'surface-container-highest': '#e5e2e1',
                'on-surface': '#1c1b1b',
                'on-surface-variant': '#514440',
                'inverse-surface': '#313030',
                'inverse-on-surface': '#f3f0ef',
                outline: '#84746f',
                'outline-variant': '#d6c2bd',
                'surface-tint': '#825343',
                'on-primary': '#ffffff',
                'primary-container': '#3d1a0e',
                'on-primary-container': '#b47e6d',
                'inverse-primary': '#f6b8a5',
                secondary: '#b02f00',
                'on-secondary': '#ffffff',
                'secondary-container': '#fc6537',
                'on-secondary-container': '#5c1400',
                tertiary: '#110f0a',
                'on-tertiary': '#ffffff',
                'tertiary-container': '#26241f',
                'on-tertiary-container': '#8f8b84',
                'on-error': '#ffffff',
                'error-container': '#ffdad6',
                'on-error-container': '#93000a',
                'primary-fixed': '#ffdbd0',
                'primary-fixed-dim': '#f6b8a5',
                'on-primary-fixed': '#321207',
                'on-primary-fixed-variant': '#663c2d',
                'secondary-fixed': '#ffdbd1',
                'secondary-fixed-dim': '#ffb59f',
                'on-secondary-fixed': '#3b0a00',
                'on-secondary-fixed-variant': '#862200',
                'tertiary-fixed': '#e8e1da',
                'tertiary-fixed-dim': '#ccc6be',
                'on-tertiary-fixed': '#1e1b17',
                'on-tertiary-fixed-variant': '#4a4641',
                background: '#fcf9f8',
                'on-background': '#1c1b1b',
                'surface-variant': '#e5e2e1',
            },

            fontFamily: {
                serif: ['Playfair Display', 'Georgia', 'serif'],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            fontSize: {
                'display-lg': ['48px', { lineHeight: '1.2', letterSpacing: '-0.02em', fontWeight: '700' }],
                'display-lg-mobile': ['36px', { lineHeight: '1.2', fontWeight: '700' }],
                'headline-lg': ['36px', { lineHeight: '1.2', fontWeight: '700' }],
                'headline-md': ['28px', { lineHeight: '1.3', fontWeight: '600' }],
                'headline-sm': ['22px', { lineHeight: '1.3', fontWeight: '600' }],
                'title-lg': ['18px', { lineHeight: '1.5', fontWeight: '600' }],
                'body-lg': ['16px', { lineHeight: '1.6', fontWeight: '400' }],
                'body-sm': ['14px', { lineHeight: '1.5', fontWeight: '400' }],
                'label-caps': ['12px', { lineHeight: '1', letterSpacing: '0.05em', fontWeight: '700' }],
            },

            borderRadius: {
                badge: '6px',
                input: '10px',
                card: '16px',
            },

            spacing: {
                xs: '4px',
                sm: '8px',
                md: '16px',
                lg: '24px',
                xl: '32px',
                xxl: '48px',
                'container-margin': '24px',
                gutter: '16px',
            },

            maxWidth: {
                container: '1200px',
            },

            boxShadow: {
                card: '0 2px 12px rgba(0, 0, 0, 0.07)',
                interactive: '0 4px 16px rgba(0, 0, 0, 0.1)',
                navbar: '0 1px 3px rgba(0, 0, 0, 0.08)',
                modal: '0 8px 40px rgba(0, 0, 0, 0.15)',
            },
        },
    },

    plugins: [forms],
};
