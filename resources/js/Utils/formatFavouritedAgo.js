/**
 * Format a favourited_at ISO date as "Added X days ago" style copy.
 */
export function formatFavouritedAgo(isoDate) {
    if (!isoDate) {
        return 'Added recently';
    }

    const date = new Date(isoDate);
    if (Number.isNaN(date.getTime())) {
        return 'Added recently';
    }

    const diffMs = Date.now() - date.getTime();
    const diffSec = Math.round(diffMs / 1000);
    const diffMin = Math.round(diffSec / 60);
    const diffHour = Math.round(diffMin / 60);
    const diffDay = Math.round(diffHour / 24);

    if (diffSec < 60) {
        return 'Added just now';
    }
    if (diffMin < 60) {
        return `Added ${diffMin} minute${diffMin === 1 ? '' : 's'} ago`;
    }
    if (diffHour < 24) {
        return `Added ${diffHour} hour${diffHour === 1 ? '' : 's'} ago`;
    }
    if (diffDay < 30) {
        return `Added ${diffDay} day${diffDay === 1 ? '' : 's'} ago`;
    }

    const diffMonth = Math.round(diffDay / 30);
    if (diffMonth < 12) {
        return `Added ${diffMonth} month${diffMonth === 1 ? '' : 's'} ago`;
    }

    const diffYear = Math.round(diffDay / 365);
    return `Added ${diffYear} year${diffYear === 1 ? '' : 's'} ago`;
}
