export function formatMoney(value: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
}

export function formatChange(value: number | null): string {
    if (value === null) {
        return '—';
    }

    const prefix = value > 0 ? '+' : '';

    return `${prefix}${value}%`;
}

export function useAdminFormat() {
    return {
        formatMoney,
        formatChange,
    };
}
