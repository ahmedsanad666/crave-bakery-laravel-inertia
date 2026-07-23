import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @param {string} variable */
function withOpacity(variable) {
    return ({ opacityValue }) => {
        if (opacityValue === undefined) {
            return `var(${variable})`;
        }

        return `color-mix(in srgb, var(${variable}) calc(${opacityValue} * 100%), transparent)`;
    };
}

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
                // Semantic aliases — runtime via CSS variables
                primary: withOpacity('--color-primary'),
                accent: withOpacity('--color-accent'),
                surface: withOpacity('--color-surface'),
                card: withOpacity('--color-card'),
                'text-primary': withOpacity('--color-text-primary'),
                'text-muted': withOpacity('--color-text-muted'),
                'border-base': withOpacity('--color-border-base'),
                success: withOpacity('--color-success'),
                error: withOpacity('--color-error'),
                warning: withOpacity('--color-warning'),
                info: withOpacity('--color-info'),

                // Material-style tokens
                'surface-dim': withOpacity('--color-surface-dim'),
                'surface-bright': withOpacity('--color-surface-bright'),
                'surface-container-lowest': withOpacity('--color-surface-container-lowest'),
                'surface-container-low': withOpacity('--color-surface-container-low'),
                'surface-container': withOpacity('--color-surface-container'),
                'surface-container-high': withOpacity('--color-surface-container-high'),
                'surface-container-highest': withOpacity('--color-surface-container-highest'),
                'on-surface': withOpacity('--color-on-surface'),
                'on-surface-variant': withOpacity('--color-on-surface-variant'),
                'inverse-surface': withOpacity('--color-inverse-surface'),
                'inverse-on-surface': withOpacity('--color-inverse-on-surface'),
                outline: withOpacity('--color-outline'),
                'outline-variant': withOpacity('--color-outline-variant'),
                'surface-tint': withOpacity('--color-surface-tint'),
                'on-primary': withOpacity('--color-on-primary'),
                'primary-container': withOpacity('--color-primary-container'),
                'on-primary-container': withOpacity('--color-on-primary-container'),
                'inverse-primary': withOpacity('--color-inverse-primary'),
                secondary: withOpacity('--color-secondary'),
                'on-secondary': withOpacity('--color-on-secondary'),
                'secondary-container': withOpacity('--color-secondary-container'),
                'on-secondary-container': withOpacity('--color-on-secondary-container'),
                tertiary: withOpacity('--color-tertiary'),
                'on-tertiary': withOpacity('--color-on-tertiary'),
                'tertiary-container': withOpacity('--color-tertiary-container'),
                'on-tertiary-container': withOpacity('--color-on-tertiary-container'),
                'on-error': withOpacity('--color-on-error'),
                'error-container': withOpacity('--color-error-container'),
                'on-error-container': withOpacity('--color-on-error-container'),
                'primary-fixed': withOpacity('--color-primary-fixed'),
                'primary-fixed-dim': withOpacity('--color-primary-fixed-dim'),
                'on-primary-fixed': withOpacity('--color-on-primary-fixed'),
                'on-primary-fixed-variant': withOpacity('--color-on-primary-fixed-variant'),
                'secondary-fixed': withOpacity('--color-secondary-fixed'),
                'secondary-fixed-dim': withOpacity('--color-secondary-fixed-dim'),
                'on-secondary-fixed': withOpacity('--color-on-secondary-fixed'),
                'on-secondary-fixed-variant': withOpacity('--color-on-secondary-fixed-variant'),
                'tertiary-fixed': withOpacity('--color-tertiary-fixed'),
                'tertiary-fixed-dim': withOpacity('--color-tertiary-fixed-dim'),
                'on-tertiary-fixed': withOpacity('--color-on-tertiary-fixed'),
                'on-tertiary-fixed-variant': withOpacity('--color-on-tertiary-fixed-variant'),
                background: withOpacity('--color-background'),
                'on-background': withOpacity('--color-on-background'),
                'surface-variant': withOpacity('--color-surface-variant'),
            },

            fontFamily: {
                serif: ['var(--font-heading)', 'Georgia', 'serif'],
                sans: ['var(--font-body)', ...defaultTheme.fontFamily.sans],
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
